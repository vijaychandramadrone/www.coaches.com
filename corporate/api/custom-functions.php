<?php

function custom_get($obj) {
    if ($obj->table=="progress_report") { //start_date, end_date, orgid or client id,  tags (optional)

        //generate either the accounting report or the progress report
        if ($obj->userType==2 || $obj->userType==1 || $obj->userType==3 ) { //progress report
            $sql = "SELECT CONCAT(cl.first_name, ' ', cl.last_name) as client, CONCAT(co.first_name, ' ', co.last_name) as coach, s.session_datetime, s.duration, (CASE status_code WHEN 1 THEN (CASE progress_notes_approved WHEN 1 THEN progress_notes ELSE 'not yet approved' END) WHEN 2 THEN 'NO SHOW' WHEN 3 THEN 'CANCELED' WHEN 4 THEN 'LATE CANCEL' WHEN 5 THEN '360 SURVEY' END) as notes FROM session_view s INNER JOIN clients_users cl ON cl.id=s.client_id INNER JOIN coachs_users co ON co.id=s.coach_id WHERE s.status_code>0 AND s.status_code < 4 AND s.client_id=" . $obj->uid[2];
            //This returns UTC time.  We could update to parse through rows and update time to accounting time...
            $obj->respondWithCSV($sql,"Coachee,Coach,Date,Duration (minutes),Notes");
        } else {
            $obj->unauthorized();
        }
        return true;

    } else if ($obj->table=="report") { //start_date, end_date, orgid or client id,  tags (optional)

        if ($obj->userType==5 || $obj->userType==1 ) { //accounting report
            //$start = $obj->uid[0];
            //$end = $obj->uid[1];

            $start = date("Y-m-d", strtotime(str_replace("-","/",$obj->uid[0])));
            $end = date("Y-m-d", strtotime(str_replace("-","/",$obj->uid[1])));

            $orgId = $obj->uid[2];
            $tags = $obj->uid[3];
            $returnCSV = false;
            if (isset($obj->uid[4]) && $obj->uid[4]=="csv") $returnCSV = true;


            if ($tags=="all") $tags = "";

            $sql = "SELECT status_code, session_datetime, session_start_datetime, session_end_datetime, duration, bill_rate, pay_rate, net_rate, coach_first_name, coachs_last_name, client_first_name, client_last_name ";
            $csvSql = "SELECT CONCAT(client_first_name,' ', client_last_name) as client_name, CONCAT(coach_first_name, ' ', coachs_last_name) as coach_name, session_start_datetime, (CASE status_code WHEN 1 THEN 'COMPLETE' WHEN 2 THEN 'NO SHOW' WHEN 3 THEN 'CANCELED' WHEN 4 THEN 'LATE-CANCEL'  END) as status, bill_rate, pay_rate, net_rate, tags ";
            $totalsSql = "SELECT SUM(bill_rate) as total_bill_rate, SUM(pay_rate) as total_pay_rate, SUM(bill_rate-pay_rate) as total_net_rate";
            $fromSql = " FROM accounting_session_view_2 WHERE organization_id=$orgId AND session_datetime>='$start' AND session_datetime<='$end' ";

            $tagSql = "";
            if (isset($tags) && $tags!="") $tagSql .= "AND LOWER(tags) LIKE LOWER('%$tags%')";
            $orderBySql = " ORDER BY session_datetime DESC ";

            $sql .= $fromSql . $tagSql . $orderBySql;
            $totalsSql .= $fromSql . $tagSql;
            $csvSql .= $fromSql . $tagSql . $orderBySql;

            if ($returnCSV) {
                //returns UTC times

                $obj->respondWithCSV($csvSql, "Coachee,Coach,SessionDate,Status,Fee,Expense,Net,Tags");
                return true;
            }

            $resource = $obj->db->executeStmt($sql);
            $all = array();
            while ($all[] = $obj->db->row($resource, $obj->userTimeZone, $obj->userDaylightSavings)) {}
            array_pop($all);

            $resource = $obj->db->executeStmt($totalsSql);
            $totalRow = $obj->db->row($resource, $obj->userTimeZone, $obj->userDaylightSavings);

            $summary = array();
            $summary["total_billed"] = $totalRow["total_bill_rate"];
            $summary["total_paid"] = $totalRow["total_pay_rate"];
            $summary["total_net"] = $totalRow["total_net_rate"];

            $ret = array("sessions"=>$all,"summary"=>$summary);

            echo json_encode($ret);
        } else {
            $obj->unauthorized();
        }

        return true;
    } else if ($obj->table=="coach_clients") {

        if ($obj->userType==1) {
            $sql = "SELECT clients_users.* FROM clients_users WHERE coach_id=" . $obj->uid[0];
        } else if ($obj->userType==2) {
            $sql = "SELECT clients_users.* FROM clients_users INNER JOIN organizations_users ON clients.organization_id=organizations_users.id WHERE organizations_users.user_id=" . $obj->userId . " AND coach_id=" . $obj->uid[0];
        } else if ($obj->userType==3) {
            $sql = "SELECT clients_users.id, clients_users.organization_id,clients_users.start_date,clients_users.phone,clients_users.sessions_allotment,clients_users.sessions_frequency,clients_users.sessions_frequency_other,clients_users.tags,clients_users.focus_area,clients_users.success_metrics,clients_users.organization_level,clients_users.first_name,clients_users.last_name,clients_users.email, o.organization_name, coachs_users.first_name as coach_first_name, coachs_users.last_name as coach_last_name FROM clients_users INNER JOIN organizations o ON o.id=clients_users.organization_id INNER JOIN coachs_users ON clients_users.coach_id=coachs_users.id WHERE coachs_users.user_id=" . $obj->userId;
        } else {
            $obj->unauthorized();
            return true;
        }
        $resource = $obj->db->executeStmt($sql);
        $all = array();
        while ($all[] = $obj->db->row($resource, $obj->userTimeZone, $obj->userDaylightSavings)) {}
        array_pop($all);
        $ret = array("clients"=>$all);
        echo json_encode($ret);
        return true;
    } else if ($obj->table=="organization_clients") {
        if ($obj->userType==1) {
            $sql = "SELECT clients_users.*, cu.first_name as coach_first_name, cu.last_name as coach_last_name  FROM clients_users LEFT JOIN coachs_users cu ON clients_users.coach_id=cu.id WHERE organization_id=" . $obj->uid[0] . " ORDER BY clients_users.last_name";
        } else if ($obj->userType==2) {
            $sql = "SELECT clients_users.*, cu.first_name as coach_first_name, cu.last_name as coach_last_name  FROM clients_users LEFT JOIN coachs_users cu ON clients_users.coach_id=cu.id INNER JOIN organizations_users ON clients_users.organization_id=organizations_users.id WHERE organizations_users.user_id=" . $obj->userId . " ORDER BY clients_users.last_name";
        } else if ($obj->userType==3) {
            $sql = "SELECT clients_users.organization_id,clients_users.start_date,clients_users.sessions_allotment,clients_users.sessions_frequency,clients_users.sessions_frequency_other,clients_users.tags,clients_users.focus_area,clients_users.success_metrics,clients_users.organization_level,clients_users.first_name,clients_users.last_name,clients_users.email, coachs_users.first_name as coach_first_name, coachs_users.last_name as coach_last_name  FROM clients_users INNER JOIN coachs_users ON clients_users.coach_id=coachs_users.id WHERE coachs_users.user_id=" . $obj->userId . " AND clients_users.organization_id=" . $obj->uid[0] . " ORDER BY clients_users.last_name";
        } else {
            $obj->unauthorized();
            return true;
        }
        //clients_users.organization_id,clients_users.start_date,clients_users.sessions_allotment,clients_users.sessions_frequency,clients_users.sessions_frequency_other,clients_users.tags,clients_users.focus_area,clients_users.success_metrics,clients_users.organization_level,clients_users.first_name,clients_users.last_name,clients_users.email
        $resource = $obj->db->executeStmt($sql);
        $all = array();
        while ($all[] = $obj->db->row($resource, $obj->userTimeZone, $obj->userDaylightSavings)) {}
        array_pop($all);
        $ret = array("clients"=>$all);
        echo json_encode($ret);
        return true;
    } else if ($obj->table == "coach_organizations") {
        if ($obj->userType==1) {
            $sql = "SELECT o.* FROM organizations o, link_organizations_coachs lnk WHERE o.id=lnk.organization_id AND lnk.coach_id=" . $obj->uid[0];
        } else {
            $obj->unauthorized();
            return true;
        }
        $resource = $obj->db->executeStmt($sql);
        $all = array();
        while ($all[] = $obj->db->row($resource, $obj->userTimeZone, $obj->userDaylightSavings)) {}
        array_pop($all);
        $ret = array("organizations"=>$all);
        echo json_encode($ret);
        return true;

    } else if ($obj->table=="client_sessions") {


        if ($obj->userType==1) {
            $sql = "SELECT * FROM session_view WHERE client_id=" . $obj->uid[0];
        } else if ($obj->userType==2) {
            $sql = "SELECT session_view.id,session_view.client_id,session_view.coach_id,session_view.session_datetime,session_view.session_start_datetime,session_view.session_end_datetime,session_view.duration,session_view.confidential_notes,session_view.progress_notes,session_view.progress_notes_approved,session_view.status_code,session_view.bill_rate FROM session_view INNER JOIN clients ON session_view.client_id=clients.id INNER JOIN organizations_users ou ON clients.organization_id=ou.id WHERE ou.user_id=" . $obj->userId . " AND clients.id=" . $obj->uid[0];
        } else if ($obj->userType==3) {
            $sql = "SELECT session_view.id,session_view.client_id,session_view.coach_id,session_view.session_datetime,session_view.session_start_datetime,session_view.session_end_datetime,session_view.duration,session_view.confidential_notes,session_view.progress_notes,session_view.progress_notes_approved,session_view.status_code,session_view.pay_rate, cl.organization_id,cl.start_date,cl.sessions_allotment,cl.sessions_frequency,cl.sessions_frequency_other,cl.tags,cl.focus_area,cl.success_metrics,cl.organization_level,cl.first_name,cl.last_name,cl.email FROM session_view INNER JOIN clients_users cl ON session_view.client_id=cl.id INNER JOIN coachs_users cu ON session_view.coach_id=cu.id WHERE cu.user_id=" . $obj->userId . " AND session_view.client_id=" . $obj->uid[0];
        } else if ($obj->userType==4) {
            $sql = "SELECT session_view.id,session_view.client_id,session_view.coach_id,session_view.session_datetime,session_view.session_start_datetime,session_view.session_end_datetime,session_view.duration,session_view.confidential_notes,session_view.progress_notes,session_view.progress_notes_approved,session_view.status_code FROM session_view INNER JOIN clients_users cu ON session_view.client_id=cu.id WHERE cu.user_id=" . $obj->userId;

        } else {
            $obj->unauthorized();
            return true;
        }
        //cl.organization_id,cl.start_date,cl.sessions_allotment,cl.sessions_frequency,cl.sessions_frequency_other,cl.tags,cl.focus_area,cl.success_metrics,cl.organization_level,cl.first_name,cl.last_name,cl.email
        $resource = $obj->db->executeStmt($sql);
        $all = array();
        while ($all[] = $obj->db->row($resource, $obj->userTimeZone, $obj->userDaylightSavings)) {}
        array_pop($all);
        $ret = array("sessions"=>$all);
        echo json_encode($ret);
        return true;
    } else if ($obj->table=="organization_sessions") {
        if ($obj->userType==1) {
            $sql = "SELECT session_view.*, cu.first_name as client_first_name, cu.last_name as client_last_name, c.first_name as coach_first_name, c.last_name as coach_last_name FROM session_view INNER JOIN clients_users cu ON session_view.client_id=cu.id LEFT JOIN coachs_users c ON session_view.coach_id=c.id WHERE cu.organization_id=" . $obj->uid[0];
        } else if ($obj->userType==2) {
            $sql = "SELECT session_view.id,session_view.bill_rate,session_view.client_id,session_view.coach_id,session_view.session_datetime,session_view.session_start_datetime,session_view.session_end_datetime,session_view.duration,session_view.confidential_notes,session_view.progress_notes,session_view.progress_notes_approved,session_view.status_code, cu.first_name as client_first_name, cu.last_name as client_last_name, c.first_name as coach_first_name, c.last_name as coach_last_name FROM session_view INNER JOIN clients_users cu ON session_view.client_id=cu.id LEFT JOIN coachs_users c ON session_view.coach_id=c.id INNER JOIN organizations_users ou ON cu.organization_id=ou.id WHERE ou.user_id=" . $obj->userId;
        } else {
            $obj->unauthorized();
            return true;
        }
       // exit($sql);

        $resource = $obj->db->executeStmt($sql);
        $all = array();
        while ($all[] = $obj->db->row($resource, $obj->userTimeZone, $obj->userDaylightSavings)) {}
        array_pop($all);
        $ret = array("sessions"=>$all);
        echo json_encode($ret);
        return true;
    } else if ($obj->table=="coach_sessions") {
        if ($obj->userType==1) {
            $sql = "SELECT * FROM session_view WHERE coach_id=" . $obj->uid[0];
        } elseif ($obj->userType==2) {
            $sql = "SELECT session_view.bill_rate,session_view.id,session_view.client_id,session_view.coach_id,session_view.session_datetime,session_view.session_start_datetime,session_view.session_end_datetime,session_view.duration,session_view.confidential_notes,session_view.progress_notes,session_view.progress_notes_approved,session_view.status_code FROM session_view INNER JOIN clients ON session_view.client_id=clients.id INNER JOIN organizations_users ou ON clients.organization_id=ou.id WHERE ou.user_id=" . $obj->userId . " AND session_view.coach_id=" . $obj->uid[0];
        } else if ($obj->userType==3) {
            $sql = "SELECT session_view.pay_rate,session_view.id,session_view.client_id,session_view.coach_id,session_view.session_datetime,session_view.session_start_datetime,session_view.session_end_datetime,session_view.duration,session_view.confidential_notes,session_view.progress_notes,session_view.progress_notes_approved,session_view.status_code, cl.first_name as client_first_name, cl.last_name as client_last_name, o.organization_name as client_organization FROM session_view INNER JOIN clients_users cl ON session_view.client_id=cl.id INNER JOIN organizations o ON cl.organization_id=o.id INNER JOIN coachs_users cu ON session_view.coach_id=cu.id WHERE cu.user_id=" . $obj->userId;
        } else {
            $obj->unauthorized();
            return true;
        }
        //session_view.id,session_view.client_id,session_view.coach_id,session_view.session_datetime,session_view.session_start_datetime,session_view.session_end_datetime,session_view.duration,session_view.confidential_notes,session_view.progress_notes,session_view.progress_notes_approved,session_view.status_code
        $resource = $obj->db->executeStmt($sql);
        $all = array();
        while ($all[] = $obj->db->row($resource, $obj->userTimeZone, $obj->userDaylightSavings)) {}
        array_pop($all);
        $ret = array("sessions"=>$all);
        echo json_encode($ret);
        return true;
    } else if ($obj->table=="organizations") {
        if ($obj->userType==1 || $obj->userType==5) {
            if (isset($obj->uid[0]) && $obj->uid[0]!="")
                $sql = "SELECT o.* FROM organizations o, link_organizations_coachs lnk WHERE o.id=lnk.organization_id AND lnk.coach_id=" . $obj->uid[0];
            else
                $sql = "SELECT * FROM organizations_users ORDER BY organization_name";
        } else if ($obj->userType==3) {
            $sql = "SELECT o.* FROM organizations_users o INNER JOIN link_organizations_coachs loc ON loc.organization_id=o.id INNER JOIN coachs_users cu ON loc.coach_id=cu.id WHERE cu.user_id=" . $obj->userId . "  ORDER BY organization_name";
        } else if ($obj->userType==4) {
            $sql = "SELECT o.* FROM organizations_users o INNER JOIN clients_users cu ON cu.organization_id=o.id WHERE cu.user_id=" . $obj->userId . "  ORDER BY organization_name";
        } else {
            $obj->unauthorized();
            return true;
        }
        $resource = $obj->db->executeStmt($sql);
        $all = array();
        while ($all[] = $obj->db->row($resource, $obj->userTimeZone, $obj->userDaylightSavings)) {}
        array_pop($all);
        $ret = array("organizations"=>$all);
        echo json_encode($ret);
        return true;
    } else if ($obj->table=="coachs") {
        if ($obj->userType==1) {
            if (!isset($obj->uid[0]) || $obj->uid[0]=="")
                $sql = "SELECT cu.*, CONCAT('!![', GROUP_CONCAT(organization_id separator ',') , ']!!') as linked_organizations FROM coachs_users cu LEFT JOIN link_organizations_coachs loc ON loc.coach_id=cu.id GROUP BY cu.id ORDER BY cu.last_name";
            else
                $sql = "SELECT coachs_users.* FROM coachs_users  INNER JOIN link_organizations_coachs loc ON loc.coach_id=coachs_users.id WHERE loc.organization_id=" . $obj->uid[0] . " ORDER BY coachs_users.last_name";
        } else if ($obj->userType==2) {
            $sql = "SELECT coachs_users.id,coachs_users.user_id,coachs_users.schedule_url,coachs_users.bio,coachs_users.bio_complete,coachs_users.expertise,coachs_users.first_name,coachs_users.last_name,coachs_users.email FROM coachs_users  INNER JOIN link_organizations_coachs loc ON loc.coach_id=coachs_users.id INNER JOIN organizations_users ou ON loc.organization_id=ou.id WHERE ou.user_id=" . $obj->userId . " ORDER BY coachs_users.last_name";
        } else if ($obj->userType==3) { //coach_users.pay_rate is no longer used.  the pay rate is always associated with the coachee/client.
            $sql = "SELECT coach_users.pay_rate,coach_users.id,coach_users.user_id,coach_users.schedule_url,coach_users.bio,coach_users.bio_complete,coach_users.expertise,coach_users.first_name,coach_users.last_name,coach_users.email FROM coachs_users WHERE user_id=" . $obj->userId . " ORDER BY coachs_users.last_name";
        } else if ($obj->userType==4) {
            $sql = "SELECT coach_users.id,coach_users.user_id,coach_users.schedule_url,coach_users.bio,coach_users.bio_complete,coach_users.expertise,coach_users.first_name,coach_users.last_name,coach_users.email FROM coachs_users INNER JOIN link_organizations_coachs loc ON loc.coach_id=coachs_users.id INNER JOIN clients_users cu ON loc.organization_id=cu.organization_id WHERE cu.user_id=" . $obj->userId . " ORDER BY coachs_users.last_name";
        } else {
            $obj->unauthorized();
            return true;
        }
        //coach_users.id,coach_users.user_id,coach_users.schedule_url,coach_users.bio,coach_users.bio_complete,coach_users.expertise,coach_users.first_name,coach_users.last_name,coach_users.email
        //echo $sql;
        $resource = $obj->db->executeStmt($sql);
        $all = array();
        while ($all[] = $obj->db->row($resource, $obj->userTimeZone, $obj->userDaylightSavings)) {}
        array_pop($all);
        $ret = array("coachs"=>$all);
        $json = json_encode($ret);
        echo str_replace("]!!\"", "]", str_replace("\"!![", "[", $json));
        return true;
    } else if ($obj->table=="documents") {
        if ($obj->userType==1) {
//            $sql = "SELECT * FROM documents WHERE client_id=" . $obj->uid[0];
            $sql = "SELECT * FROM client_documents cd WHERE cd.documentTemplate_id IN ( SELECT lodt.documentTemplate_id FROM clients INNER JOIN link_organizations_documentTemplates lodt ON lodt.organization_id=clients.organization_id  AND clients.id=" . $obj->uid[0] . " INNER JOIN documentTemplates dt ON lodt.documentTemplate_id=dt.id WHERE dt.confidential!=1) AND (cd.client_id=" . $obj->uid[0] . " OR cd.client_id IS NULL)";

        } else if ($obj->userType==2) {
//            $sql = "SELECT d.* FROM documents d INNER JOIN clients ON d.client_id=clients.id INNER JOIN organizations_users ou ON clients.organization_id=ou.id WHERE ou.user_id=" . $obj->userId . " AND d.client_id=" . $obj->uid[0];
            $sql = "SELECT * FROM client_documents cd WHERE cd.documentTemplate_id IN ( SELECT lodt.documentTemplate_id FROM link_organizations_documentTemplates lodt INNER JOIN documentTemplates dt ON lodt.documentTemplate_id=dt.id INNER JOIN organizations o ON o.id = lodt.organization_id INNER JOIN users ON users.id=o.user_id WHERE dt.confidential!=1 AND users.id=" . $obj->userId . ") AND (cd.client_id=" . $obj->uid[0] . " OR cd.client_id IS NULL)";

        } else if ($obj->userType==3) {
            $sql = "SELECT * FROM (SELECT * FROM client_documents cd WHERE cd.documentTemplate_id IN ( SELECT lodt.documentTemplate_id FROM clients INNER JOIN link_organizations_documentTemplates lodt ON lodt.organization_id=clients.organization_id  AND clients.id=" . $obj->uid[0] . " ) AND (cd.client_id=" . $obj->uid[0] . " OR cd.client_id IS NULL)) cdd LEFT JOIN clients ON clients.id=cdd.client_id LEFT JOIN coachs_users cu ON clients.coach_id=cu.id WHERE (cu.user_id=" . $obj->userId . " AND cdd.client_id=" . $obj->uid[0] . ") OR cdd.client_id IS NULL";
        } else if ($obj->userType==4) {
            $sql = "SELECT DISTINCT * FROM (SELECT * FROM client_documents cd WHERE cd.documentTemplate_id IN ( SELECT lodt.documentTemplate_id FROM clients_users cu INNER JOIN link_organizations_documentTemplates lodt ON lodt.organization_id=cu.organization_id  AND cu.user_id=" . $obj->userId . " )) a "; //removed this because it was creating a problem: LEFT JOIN clients_users cuu ON cuu.id=a.client_id
            $rId = $obj->userId;
        } else {
            $obj->unauthorized();
            return true;
        }
        $obj->logToFile($sql);
        $resource = $obj->db->executeStmt($sql);
        $all = array();
        while ($all[] = $obj->db->row($resource, $obj->userTimeZone, $obj->userDaylightSavings)) {}
        array_pop($all);
        $ret = array("documents"=>$all);
        echo json_encode($ret);
        return true;
    } else if ($obj->table=="documentTemplates") {
        if ($obj->userType==1) {

            $sql = "SELECT  dt.*, CONCAT('!![', GROUP_CONCAT(organization_id separator ',') , ']!!') as linked_organizations FROM documentTemplates dt LEFT JOIN link_organizations_documentTemplates lnk ON lnk.documentTemplate_id=dt.id";
            if (isset($obj->uid[0]) && $obj->uid[0]!="") $sql .= " WHERE lnk.organization_id=" . $obj->uid[0];
            $sql .= " GROUP BY dt.id";

        } else if ($obj->userType==2) {
            $sql = "SELECT dt.* FROM documentTemplates dt INNER JOIN link_organizations_documentTemplates lnk ON lnk.documentTemplate_id=dt.id INNER JOIN organizations_users ou ON lnk.organization_id=ou.id WHERE ou.user_id=" . $obj->userId;
        } else if ($obj->userType==3) {
            $sql = "SELECT dt.* FROM documentTemplates dt INNER JOIN link_organizations_documentTemplates lnk ON lnk.documentTemplate_id=dt.id INNER JOIN link_organizations_coachs loc ON lnk.organization_id=loc.organization_id INNER JOIN coachs_users cu ON loc.coach_id=cu.id WHERE loc.organization_id=" . $obj->uid[0] . " AND cu.user_id=" . $obj->userId;
        } else if ($obj->userType==4) {
            $sql = "SELECT dt.* FROM documentTemplates dt INNER JOIN link_organizations_documentTemplates lnk ON lnk.documentTemplate_id=dt.id INNER JOIN organizations ON lnk.organization_id=organizations.id INNER JOIN clients_users cu ON organizations.id=cu.organization_id WHERE cu.user_id=" . $obj->userId;
        } else {
            $obj->unauthorized();
            return true;
        }

        $obj->logToFile("DT SQL: " . $sql);

        $resource = $obj->db->executeStmt($sql);
        $all = array();
        while ($all[] = $obj->db->row($resource, $obj->userTimeZone, $obj->userDaylightSavings)) {}
        array_pop($all);
        $ret = array("documentTemplates"=>$all);
        $json = json_encode($ret);
        echo str_replace("]!!\"", "]", str_replace("\"!![", "[", $json));
        return true;
    } else if ($obj->table=="organization_stats") {
        if ($obj->userType==1) {
            $orgId = $obj->uid[0];
        } else if ($obj->userType==2) {
            $orgId = $obj->user_to_table_id("organizations", $obj->userId);
        }
        $ret = array();
        $sql = "SELECT o.budget, SUM(sessions.bill_rate) as total_bill_rate FROM sessions, clients cl, organizations o WHERE o.id=cl.organization_id AND cl.id=sessions.client_id AND sessions.status_code!=3 AND sessions.status_code!=5 AND sessions.status_code!=0 AND cl.organization_id=$orgId GROUP BY cl.organization_id";
        $resource = $obj->db->executeStmt($sql);

        if ($obj->db->numRows($resource) == 0) {
            //no sessions exist yet...
            $sql = "SELECT budget FROM organizations WHERE id=$orgId";
            $resource = $obj->db->executeStmt($sql);
            $totalRow = $obj->db->row($resource, $obj->userTimeZone, $obj->userDaylightSavings);

            $ret["stats"] = array();
            $ret["stats"]["budget"] = array();
            $ret["stats"]["budget"]["total"] = $totalRow["budget"];
            $ret["stats"]["budget"]["used"] = 0;
            $ret["stats"]["budget"]["balance"] = $totalRow["budget"];

        } else {

            $totalRow = $obj->db->row($resource, $obj->userTimeZone, $obj->userDaylightSavings);
            $ret["stats"] = array();
            $ret["stats"]["budget"] = array();
            $ret["stats"]["budget"]["total"] = $totalRow["budget"];
            $ret["stats"]["budget"]["used"] = $totalRow["total_bill_rate"];
            $ret["stats"]["budget"]["balance"] = $ret["stats"]["budget"]["total"] - $ret["stats"]["budget"]["used"];
        }

        $ret["stats"]["progress"] = array();

        $sql = "SELECT SUM(CASE WHEN cl.coach_id IS NOT NULL THEN 1 ELSE 0 END) as m1, COUNT(cl.id) as total FROM clients cl WHERE cl.organization_id=$orgId";
        $resource = $obj->db->executeStmt($sql);
        $progressRow = $obj->db->row($resource, $obj->userTimeZone, $obj->userDaylightSavings);
        $sql = "SELECT SUM(CASE WHEN session_count > 0 THEN 1 ELSE 0 END) as m2, SUM(CASE WHEN session_count >= qty / 2 THEN 1 ELSE 0 END) as m3, SUM(CASE WHEN session_count >= qty THEN 1 ELSE 0 END) as m4, COUNT(session_count) as total FROM (SELECT COUNT(s.id)  as session_count, cl.sessions_allotment as qty FROM clients cl LEFT JOIN sessions s ON cl.id=s.client_id WHERE cl.organization_id=$orgId AND s.status_code!=3  AND s.status_code!=5 AND s.status_code!=0 GROUP BY cl.id) as cl2";
        $resource = $obj->db->executeStmt($sql);
        $progressRow2 = $obj->db->row($resource, $obj->userTimeZone, $obj->userDaylightSavings);

        if ($progressRow["total"]==0) {
            $ret["stats"]["progress"]["milestone_1"] = 0;
            $ret["stats"]["progress"]["milestone_2"] = 0;
            $ret["stats"]["progress"]["milestone_3"] = 0;
            $ret["stats"]["progress"]["milestone_4"] = 0;
        } else {
            $ret["stats"]["progress"]["milestone_1"] = round(100 * $progressRow["m1"] / $progressRow["total"]);
            $ret["stats"]["progress"]["milestone_2"] = round(100 * $progressRow2["m2"] / $progressRow["total"]);
            $ret["stats"]["progress"]["milestone_3"] = round(100 * $progressRow2["m3"] / $progressRow["total"]);
            $ret["stats"]["progress"]["milestone_4"] = round(100 * $progressRow2["m4"] / $progressRow["total"]);
        }
        $sql = "SELECT COUNT(id) as client_count FROM clients WHERE organization_id=$orgId";
        $resource = $obj->db->executeStmt($sql);
        $clientRow = $obj->db->row($resource, $obj->userTimeZone, $obj->userDaylightSavings);
        $sql = "SELECT COUNT(coach_id) as coach_count FROM link_organizations_coachs WHERE organization_id=$orgId";
        $resource = $obj->db->executeStmt($sql);
        $coachRow = $obj->db->row($resource, $obj->userTimeZone, $obj->userDaylightSavings);
        $ret["stats"]["counts"] = array();
        $ret["stats"]["counts"]["coach"] = $coachRow["coach_count"];
        $ret["stats"]["counts"]["client"] = $clientRow["client_count"];

       echo json_encode($ret);

        return true;
    }
    return false;
}

function custom_post($obj) {
    if ($obj->table=="upload" || $obj->table=="valums-upload") {

        $obj->logToFile("Valums Upload");
        if ($obj->userType!=1&&$obj->userType!=3&&$obj->userType!=4)  $obj->unauthorized();

        $folderId = "";
        if ($obj->userType==1) {
            $folderId = "documentTemplates/";
        } else if ($obj->userType==3) {
            $folderId= $_GET["document_id"] . "/" . $_GET["client_id"] . "/";
        } else if ($obj->userType==4) {
            $resource = $obj->db->executeStmt("SELECT id FROM clients WHERE user_id=" . $obj->userId);
            $row = $obj->db->row($resource, $obj->userTimeZone, $obj->userDaylightSavings);
            if (!$row) $folderId= $_GET["document_id"] . "/" . $obj->userId . "/";
            else $folderId = $_GET["document_id"] . "/" . $row["id"] . "/";
        }

        $target_path = "../uploads/$folderId";
        if (!is_dir($target_path)) {mkdir($target_path,0777,true);}


        // list of valid extensions, ex. array("jpeg", "xml", "bmp")
        $allowedExtensions = array();
        // max file size in bytes
        $sizeLimit = 8 * 1024 * 1024;

        require('valums-file-uploader.php');
        $uploader = new qqFileUploader($allowedExtensions, $sizeLimit);

        // Call handleUpload() with the name of the folder, relative to PHP's getcwd()
        $result = $uploader->handleUpload($target_path);

        if (isset($result["success"]) && $result["success"]) { //success

            $filename = $uploader->getName();
            $dotPosition = strpos($filename, '.');
            $extension = "";
            if ($dotPosition !== FALSE) {
                $extension = substr($filename, $dotPosition + 1);
                $filename = substr($filename, 0, $dotPosition);
            }
            //$filename = preg_replace("/[^a-zA-Z0-9]/", "_", $filename);
            if ($extension!="") $filename.=".$extension";

            $target_path = $target_path . basename($filename);

            $target_url_db = $obj->config['settings']['baseURL'] . "/uploads/$folderId" . $filename;
            $target_url = urlencode($target_url_db);


            if ($obj->userType==4) { //client
                $templateId = $_GET["document_id"];
                $obj->db->executeStmt("INSERT INTO documents (client_id, documentTemplate_id, title, url) VALUES ((SELECT id FROM clients WHERE user_id=$obj->userId),$templateId,'$filename','$target_url_db') ON DUPLICATE KEY UPDATE title='$filename', url='$target_url_db'");
            } else if ($obj->userType==3) { //coach
                $clientId=$_GET["client_id"];
                $templateId = $_GET["document_id"];
                $obj->db->executeStmt("INSERT INTO documents (client_id, documentTemplate_id, title, url) VALUES ($clientId, $templateId, '$filename','$target_url_db') ON DUPLICATE KEY UPDATE title='$filename', url='$target_url_db'");
            } else if ($obj->userType==1){
                if (isset($_GET["title"])) $title = $_GET["title"]; else $title = $filename;
                if (isset($_GET["readonly"])) $readonly = $_GET["readonly"]; else $readonly = 0;
                if (isset($_GET["confidential"]))$confidential = $_GET["confidential"]; else $confidential = 0;
                $sql = "INSERT INTO documentTemplates (title, url, readonly, confidential) VALUES ('$title','$target_url_db', $readonly, $confidential)";
                $obj->logToFile($sql);
                $obj->db->executeStmt($sql);
            }

            $obj->logToFile("Added Document Id: " . $obj->db->lastInsertId());
            $GLOBALS["override_content_type"] = "text/html";
            if ($obj->db->lastInsertId()==0)
                $obj->respondWithSuccessObject('false', "-98", "Error: Unable to add file to database.");
            else
                $obj->respondWithSuccessObject('true', "id:".$obj->db->lastInsertId());

        } else{

            $obj->respondWithSuccessObject('false', "-99", "Error with file upload: " . $result["error"]);

        }
    } else if ($obj->table=="logo-upload") {
        $obj->logToFile("Logo Upload");
        if ($obj->userType!=1)  $obj->unauthorized();

        if (!isset($_GET["organizationId"])) {
            $obj->respondWithSuccessObject('false', "-98", "Error: Organization id not set.");
            return;
        }

        $orgId = $_GET["organizationId"];

        $folderId = "logos/";

        $target_path = "../uploads/$folderId";
        if (!is_dir($target_path)) {mkdir($target_path,0777,true);}

        // list of valid extensions, ex. array("jpeg", "xml", "bmp")
        $allowedExtensions = array('jpg', 'jpeg', 'png', 'gif');
        // max file size in bytes
        $sizeLimit = 8 * 1024 * 1024;

        require('valums-file-uploader.php');
        $uploader = new qqFileUploader($allowedExtensions, $sizeLimit);

        // Call handleUpload() with the name of the folder, relative to PHP's getcwd()
        $result = $uploader->handleUpload($target_path);

        if (isset($result["success"]) && $result["success"]) { //success
            $filename = $uploader->getName();
            $dotPosition = strpos($filename, '.');
            $extension = "";
            if ($dotPosition !== FALSE) {
                $extension = substr($filename, $dotPosition + 1);
                $filename = substr($filename, 0, $dotPosition);
            }
            $filename = preg_replace("/[^a-zA-Z0-9]/", "_", $filename);
            if ($extension!="") $filename.=".$extension";

            $target_path = $target_path . basename($filename);

            $target_url_db = $obj->config['settings']['baseURL'] . "/uploads/$folderId" . $filename;
            $target_url = $target_url_db;

            $sql = "UPDATE organizations SET logo='$target_url' WHERE id=$orgId";
            $obj->logToFile($sql);
            $obj->db->executeStmt($sql);

            $obj->logToFile("Added Logo");
            $GLOBALS["override_content_type"] = "text/html";
            if ($obj->db->numAffected() > 0)
                $obj->respondWithSuccessObject('true', "", "Logo update successful");
            else
                $obj->respondWithSuccessObject('false', "-98", "Error: Unable to locate organization.");
        } else{
           // echo print_r($result);
            $obj->respondWithSuccessObject('false', "-99", "Error with file upload: " . $result["error"]);
        }
    } else if ($obj->table=="import") {

        $obj->logToFile("Import Upload");
        if ($obj->userType!=1)  $obj->unauthorized();

        $folderId = "imports/";

        $target_path = "../uploads/$folderId";
        if (!is_dir($target_path)) {mkdir($target_path,0777,true);}

        // list of valid extensions, ex. array("jpeg", "xml", "bmp")
        $allowedExtensions = array('csv');
        // max file size in bytes
        $sizeLimit = 8 * 1024 * 1024;

        require('valums-file-uploader.php');
        $uploader = new qqFileUploader($allowedExtensions, $sizeLimit);
        $obj->logToFile("Uploader Initialized.  Path: $target_path");

        // Call handleUpload() with the name of the folder, relative to PHP's getcwd()
        $result = $uploader->handleUpload($target_path);

        $obj->logToFile("Upload Results:" . $result["success"]);

        if (isset($result["success"]) && $result["success"]) { //success
            $filename = $uploader->getName();
            $dotPosition = strpos($filename, '.');
            $extension = "";
            if ($dotPosition !== FALSE) {
                $extension = substr($filename, $dotPosition + 1);
                $filename = substr($filename, 0, $dotPosition);
            }
            //$filename = preg_replace("/[^a-zA-Z0-9]/", "_", $filename);
            if ($extension!="") $filename.=".$extension";

            $target_path = $target_path . basename($filename);

            $obj->logToFile("Processing file $target_path");

            ini_set("auto_detect_line_endings", true);
            $csv = array();
            $file    = fopen($target_path, "r");
            $obj->logToFile("File open for reading.");

            mysql_query("SET AUTOCOMMIT=0");
            mysql_query("START TRANSACTION");
            $obj->logToFile("Begin SQL Transaction.");

            while ($line = fgetcsv($file)) {
                array_push($csv, $line);
               // $obj->logToFile($line);
            }
            fclose($file);

            $keys = array_shift($csv);

            $obj->logToFile(count($csv) . " lines read.");

            foreach ($csv as $i=>$row) {
                $csv[$i] = array_combine($keys, $row);
                if ($csv[$i]['email']!="" && $csv[$i]['organization_id']!="") {
                    $sql = "INSERT INTO users (`user_type_id`,`userType`,`username`,`first_name`,`last_name`,`phone`) VALUES (4, 4,'".$csv[$i]['email']."','".$csv[$i]['first_name']."','".$csv[$i]['last_name']."','".$csv[$i]['phone']."' )";
                    //echo $sql;
                    $obj->logToFile($sql);
                    if (!mysql_query($sql)) {
                        //error...
                        mysql_query("ROLLBACK");
                        $obj->logToFile("Rollback SQL Transaction.");
                        $obj->respondWithSuccessObject('false', "-" . mysql_errno(), "Error importing data.  No records added. (" . mysql_error() . ")");
                        return;
                    } else {
                        $userId = mysql_insert_id();
                        $sql = "INSERT INTO clients (`user_id`,`organization_id`,`coach_id`,`start_date`,`sessions_allotment`,`sessions_frequency`,`tags`,`focus_area`,`success_metrics`,`organization_level`,`bill_rate`,`sessions_frequency_other`,`pay_rate`) VALUES ('$userId','".$csv[$i]['organization_id']."',NULL,'".$csv[$i]['start_date']."','".$csv[$i]['sessions_allotment']."','".$csv[$i]['sessions_frequency']."','".$csv[$i]['tags']."','".$csv[$i]['focus_area']."','".$csv[$i]['success_metrics']."','".$csv[$i]['organization_level']."','".$csv[$i]['bill_rate']."','".$csv[$i]['sessions_frequency_other']."','".$csv[$i]['pay_rate']."')";
                        //echo $sql;
                        $obj->logToFile($sql);
                        if (!mysql_query($sql)) {
                            //error...
                            mysql_query("ROLLBACK");
                            $obj->logToFile("Rollback SQL Transaction.");
                            $obj->respondWithSuccessObject('false', "-" . mysql_errno(), "Error importing data.  No records added. (" . mysql_error() . ")");
                            return;
                        }
                    }
                } else {
                    $obj->logToFile("Row skipped due to missing email or organization id.");
                }
            }
            mysql_query("COMMIT");
            $obj->logToFile("Committed SQL Transaction.");
            $obj->respondWithSuccessObject('true', "", "Import successful" . $target_path);
        } else{
              $obj->respondWithSuccessObject('false', "-99", "Error with file upload: " . $result["error"]);
        }
     } else if ($obj->table=="link_organizations_to_documentTemplate") {

        if ($obj->userType==1) {

            $dTemplateId = $_POST["documentTemplate_id"];
            $orgIds = explode("|", $_POST["organization_ids"]);
            $sql = "DELETE FROM link_organizations_documentTemplates WHERE documentTemplate_id=$dTemplateId";
            //echo "$sql\n";
            $obj->db->executeStmt($sql);
            $sql = "INSERT INTO link_organizations_documentTemplates (documentTemplate_id, organization_id) VALUES ";
            foreach($orgIds as $orgId) {
                $sql .= "($dTemplateId, $orgId),";
            }
            $sql = substr($sql, 0, -1);
            //echo $sql . "\n\n";
            $obj->db->executeStmt($sql);
            if ($obj->db->getError()!=null) $obj->respondWithSuccessObject('false',$obj->db->getError(), "Error: Unable to link document to organization(s).");

            $obj->respondWithSuccessObject('true', "Document Linked Successfully");
        } else {
            $obj->unauthorized();
            return true;
        }
    } else if ($obj->table=="link_organizations_to_coach") {
        if ($obj->userType==1) {

            $coachId = $_POST["coach_id"];
            $orgIds = explode("|", $_POST["organization_ids"]);
            $emailsToSend = array();
            $existingOrgIds = array();
            $resource = $obj->db->executeStmt("SELECT organization_id FROM link_organizations_coachs WHERE coach_id=$coachId");
            while ($row = $obj->db->row($resource, $obj->userTimeZone, $obj->userDaylightSavings)) array_push($existingOrgIds, $row["organization_id"]);

            foreach($orgIds as $orgId) {
                if (in_array($orgId, $existingOrgIds, true)) {
                    $obj->logToFile("Email not sent because org already associated with coach" . $row["organization_id"] . ".");
                } else {
                    $obj->logToFile("Email queued" . $row["organization_id"] . ".");
                    array_push($emailsToSend, $orgId);
                }
            }


             $sql = "DELETE FROM link_organizations_coachs WHERE coach_id=$coachId";
            $obj->db->executeStmt($sql);
            if (count($orgIds)>0 && $orgIds[0]!="") {
                $sql = "INSERT INTO link_organizations_coachs (coach_id, organization_id) VALUES ";
                foreach($orgIds as $orgId) {
                    $sql .= "($coachId, $orgId),";
                }
                $sql = substr($sql, 0, -1);
                //echo $sql . "\n\n";
                $obj->db->executeStmt($sql);
                if ($obj->db->getError()!=null) $obj->respondWithSuccessObject('false',$obj->db->getError(), "Error linking coach to organizations.");
            }

            //send emails
            foreach($emailsToSend as $orgId) {
                if (!send_coach_organization_notification_mail($obj, $coachId, $orgId, $errmsg)) {
                    $obj->logToFile("Error sending organization notification email to coach.");
                } else {
                    $obj->logToFile("Email sent to coach $coachId re: org $orgId");
                }
            }

            $obj->respondWithSuccessObject('true', "Coaches linked Successfully");
            return true;

        } else {
            echo "{\"message\":\"" . $obj->userType . "\"}";
            $obj->unauthorized();
            return true;
        }
    } else if ($obj->table=="users") {
        if (isset($_POST["timezone"])) {
            if (is_numeric($_POST["timezone"])) {
                $tz = $_POST["timezone"];
                $dls = 1;
                if (!isset($_POST["daylight-savings"]) || $_POST["daylight-savings"]!=1) $dls = 0;
                $obj->db->executeStmt("UPDATE users SET timezone=$tz, daylight_savings=$dls WHERE id=" . $obj->userId);
                if ($obj->db->numAffected() > 0)
                    $obj->respondWithSuccessObject('true', "Timezone updated successfully");
                else
                    $obj->respondWithSuccessObject('true', "No update required or user not found.");
                return true;
            } else {
                $obj->respondWithSuccessObject('false', "-95", "Error: Timezone formatted incorrectly.");
            }
        } else {
            $obj->unauthorized();
            return true;
        }

    } else if ($obj->table=="report_errors") {

        $jsonStr = json_decode($_POST["errors"]);

        foreach ($jsonStr as $err) {
            $sql = "INSERT INTO error_log (username, sessionId, browserUserAgent, browserAppVersion, jqxhrResponseText, jqxhrStatus, settingsType, settingsUrl, exception, context, function, code) VALUES ('" . $err->user . "','" . $err->sessionId  . "','" . $err->browserUserAgent  . "','" . $err->browserAppVersion  . "','" . $err->jqxhrResponseText  . "','" . $err->jqxhrStatus  . "','" . $err->settingsType  . "','" . $err->settingsUrl  . "','" . $err->exception . "','" . $err->context . "','" . $err->fnction . "','" . $err->code . "');";

            $obj->db->executeStmt($sql);
            if ($obj->db->getError()!=null) {
                $obj->logToFile("Error writing error to error log table (" . $obj->db->getError() . ").  " . $sql);
                $obj->respondWithSuccessObject('false',$obj->db->getError(), "Error reporting errors.");
                return true;
            }
        }
        $obj->respondWithSuccessObject('true',"", "Success recording errors.");
        return true;
    } else if ($obj->table=="testNotAuthorized") {
        $obj->unauthorized();
        return true;
    }

}




?>