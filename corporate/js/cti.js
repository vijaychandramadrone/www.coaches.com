/* Copyright ConnectedAdvantage.com
For use only with the CTI Corporate Engagement Portal

Notes:

Date format is MySQL standard Y-m-d G:i:s
Coaches is intentionally spelled wrong: coachs
1 indicates true/yes, 0 indicates false/no
TODO: Make responses more consistent and informative.  It now returns the last SQL statement on many errors.  The error code is often the MySQL error and can be Googled.
budget field is in dollars (rounded no cents)
duration field is in minutes
constants are defined below
 */

var SERVER_URI = 'http://www.project-files.net/cti/api';
var MQL_URI = 'http://www.project-files.net/cti/mql/index.php';

var SESSION_STATUS = {
    UPCOMING: 0,
    COMPLETE : 1,
    NOSHOW: 2,
    CANCELLED : 3,
    OTHER: 4
};
var USER_TYPE = {
    ACCOUNT_MANAGER: 1,
    ORGANIZATION_SPONSOR : 2,
    COACH: 3,
    CLIENT : 4,
    ACCOUNTING: 5
};

var session_id="";
var userType = 0;
var thedata = {"organizations":[{"id":"1","organization_name":"Microsoft","user_id":"2","addr_street":"4667 Mission Street","addr_city":"San Francisco","addr_state":"CA","addr_zip":"94110","notes":"some notes on this company","budget":"50000","first_name":"H.R.","last_name":"Nike","email":"hr@nike.com","coachs":[{"id":"1","user_id":"3","schedule_url":"http:\/\/www.timetrade.com","bio":"Here is my bio...","bio_complete":"1","expertise":"","pay_rate":"0"}],"documentTemplates":[{"id":"1","title":"facilisi. Sed neque. Sed","url":"http:\/\/www.mydocumentlink.com\/template","readonly":"0","confidential":"0"},{"id":"2","title":"eu, ultrices sit","url":"http:\/\/www.mydocumentlink.com\/template","readonly":"0","confidential":"0"}],"clients":[{"id":"1","coach_id":"1","organization_id":"1","user_id":"4","start_date":"2012-06-14","sessions_allotment":"16","sessions_frequency":"Weekly","tags":"SanJose","focus_area":"Becoming a better leader","success_metrics":"A new level in sales and employee engagement.","organization_level":"Executive Vice President","bill_rate":"0","first_name":"Mrs.","last_name":"Client","email":"client@nike.com","coachs":[{"id":"1","user_id":"3","schedule_url":"http:\/\/www.timetrade.com","bio":"Here is my bio...","bio_complete":"1","expertise":"","pay_rate":"0","first_name":"Jeremy","last_name":"Stover","email":"jeremy@connectedadvantage.com"}],"documents":[{"id":"78","client_id":"1","title":"008_MyCalmBeat_Gateway.xlsx","url":"\/cti\/uploads\/008_MyCalmBeat_Gateway.xlsx","documentTemplate_id":"1","documentTemplates":[{"id":"1","title":"facilisi. Sed neque. Sed","url":"http:\/\/www.mydocumentlink.com\/template","readonly":"0","confidential":"0"}]}],"sessions":[{"id":"1","client_id":"1","coach_id":"1","session_datetime":"2013-01-01 06:16:29","confidential_notes":"this is the confidential note","progress_notes":"Here is my progress","progress_notes_approved":"1","status_code":"1","duration":"40","coachs":[{"id":"1","user_id":"3","schedule_url":"http:\/\/www.timetrade.com","bio":"Here is my bio...","bio_complete":"1","expertise":"","pay_rate":"0","first_name":"Jeremy","last_name":"Stover","email":"jeremy@connectedadvantage.com"}]}]},{"coachs":[{"id":"1","user_id":"3","schedule_url":"http:\/\/www.timetrade.com","bio":"Here is my bio...","bio_complete":"1","expertise":"","pay_rate":"0","first_name":"Jeremy","last_name":"Stover","email":"jeremy@connectedadvantage.com"}],"documents":[{"id":"78","client_id":"1","title":"008_MyCalmBeat_Gateway.xlsx","url":"\/cti\/uploads\/008_MyCalmBeat_Gateway.xlsx","documentTemplate_id":"1","documentTemplates":[{"id":"1","title":"facilisi. Sed neque. Sed","url":"http:\/\/www.mydocumentlink.com\/template","readonly":"0","confidential":"0"}]},{"documentTemplates":[{"id":"1","title":"facilisi. Sed neque. Sed","url":"http:\/\/www.mydocumentlink.com\/template","readonly":"0","confidential":"0"}]},{"id":"79","client_id":"1","title":"build.xml","url":"\/cti\/uploads\/build.xml","documentTemplate_id":"2","documentTemplates":[{"id":"2","title":"eu, ultrices sit","url":"http:\/\/www.mydocumentlink.com\/template","readonly":"0","confidential":"0"}]}],"sessions":[{"id":"26","client_id":"1","coach_id":"3","session_datetime":"2012-06-12 15:00:00","confidential_notes":"This note is confidential.","progress_notes":"This is my success.","progress_notes_approved":"0","status_code":"1","duration":"40"},{"id":"27","client_id":"1","coach_id":"3","session_datetime":"2012-06-12 15:00:00","confidential_notes":"This note is confidential.","progress_notes":"This is my success.","progress_notes_approved":"0","status_code":"1","duration":"40"},{"id":"28","client_id":"1","coach_id":"3","session_datetime":"2012-06-12 15:00:00","confidential_notes":"This note is confidential.","progress_notes":"This is my success.","progress_notes_approved":"0","status_code":"1","duration":"40"},{"id":"29","client_id":"1","coach_id":"3","session_datetime":"2012-06-12 15:00:00","confidential_notes":"This note is confidential.","progress_notes":"This is my success.","progress_notes_approved":"0","status_code":"1","duration":"40"},{"id":"30","client_id":"1","coach_id":"3","session_datetime":"2012-06-12 15:00:00","confidential_notes":"This note is confidential.","progress_notes":"This is my success.","progress_notes_approved":"0","status_code":"1","duration":"40"},{"id":"31","client_id":"1","coach_id":"3","session_datetime":"2012-06-12 15:00:00","confidential_notes":"This note is a confidential note.","progress_notes":"This is my success.","progress_notes_approved":"0","status_code":"1","duration":"40"},{"id":"32","client_id":"1","coach_id":"3","session_datetime":"2012-06-12 15:00:00","confidential_notes":"This note is confidential.","progress_notes":"This is my success.","progress_notes_approved":"1","status_code":"1","duration":"40"},{"id":"33","client_id":"1","coach_id":"3","session_datetime":"2012-06-12 15:00:00","confidential_notes":"This note is confidential.","progress_notes":"This is my success.","progress_notes_approved":"1","status_code":"1","duration":"40"},{"id":"34","client_id":"1","coach_id":"3","session_datetime":"2012-06-12 15:00:00","confidential_notes":"This note is s confidential.","progress_notes":"This is my success.","progress_notes_approved":"0","status_code":"1","duration":"40"},{"id":"35","client_id":"1","coach_id":"3","session_datetime":"2012-06-12 15:00:00","confidential_notes":"This note is ff confidential.","progress_notes":"This is my success.","progress_notes_approved":"1","status_code":"1","duration":"40"},{"id":"1","client_id":"1","coach_id":"1","session_datetime":"2013-01-01 06:16:29","confidential_notes":"this is the confidential note","progress_notes":"Here is my progress","progress_notes_approved":"1","status_code":"1","duration":"40","coachs":[{"id":"1","user_id":"3","schedule_url":"http:\/\/www.timetrade.com","bio":"Here is my bio...","bio_complete":"1","expertise":"","pay_rate":"0","first_name":"Jeremy","last_name":"Stover","email":"jeremy@connectedadvantage.com"}]},{"":null},{"id":"26","client_id":"1","coach_id":"3","session_datetime":"2012-06-12 15:00:00","confidential_notes":"This note is confidential.","progress_notes":"This is my success.","progress_notes_approved":"0","status_code":"1","duration":"40","coachs":[]},{"id":"27","client_id":"1","coach_id":"3","session_datetime":"2012-06-12 15:00:00","confidential_notes":"This note is confidential.","progress_notes":"This is my success.","progress_notes_approved":"0","status_code":"1","duration":"40","coachs":[]},{"id":"28","client_id":"1","coach_id":"3","session_datetime":"2012-06-12 15:00:00","confidential_notes":"This note is confidential.","progress_notes":"This is my success.","progress_notes_approved":"0","status_code":"1","duration":"40","coachs":[]},{"id":"29","client_id":"1","coach_id":"3","session_datetime":"2012-06-12 15:00:00","confidential_notes":"This note is confidential.","progress_notes":"This is my success.","progress_notes_approved":"0","status_code":"1","duration":"40","coachs":[]},{"id":"30","client_id":"1","coach_id":"3","session_datetime":"2012-06-12 15:00:00","confidential_notes":"This note is confidential.","progress_notes":"This is my success.","progress_notes_approved":"0","status_code":"1","duration":"40","coachs":[]},{"id":"31","client_id":"1","coach_id":"3","session_datetime":"2012-06-12 15:00:00","confidential_notes":"This note is a confidential note.","progress_notes":"This is my success.","progress_notes_approved":"0","status_code":"1","duration":"40","coachs":[]},{"id":"32","client_id":"1","coach_id":"3","session_datetime":"2012-06-12 15:00:00","confidential_notes":"This note is confidential.","progress_notes":"This is my success.","progress_notes_approved":"1","status_code":"1","duration":"40","coachs":[]},{"id":"33","client_id":"1","coach_id":"3","session_datetime":"2012-06-12 15:00:00","confidential_notes":"This note is confidential.","progress_notes":"This is my success.","progress_notes_approved":"1","status_code":"1","duration":"40","coachs":[]},{"id":"34","client_id":"1","coach_id":"3","session_datetime":"2012-06-12 15:00:00","confidential_notes":"This note is s confidential.","progress_notes":"This is my success.","progress_notes_approved":"0","status_code":"1","duration":"40","coachs":[]}]},{"id":"45","coach_id":"-1","organization_id":"1","user_id":"44","start_date":"2012-01-02","sessions_allotment":"33","sessions_frequency":"Every other week","tags":"sanjose","focus_area":"this is my focus area","success_metrics":"this is how I will measure success","organization_level":"vp","bill_rate":"0","first_name":"new","last_name":"client","email":"new4739@nike.com","coachs":[],"documents":[],"":null,"documentTemplates":[{"id":"2","title":"eu, ultrices sit","url":"http:\/\/www.mydocumentlink.com\/template","readonly":"0","confidential":"0"},{"id":null,"title":null,"url":null,"readonly":null,"confidential":null}]},{"":null,"sessions":[]},{"id":"46","coach_id":"-1","organization_id":"1","user_id":"45","start_date":"2012-01-02","sessions_allotment":"33","sessions_frequency":"Every other week","tags":"sanjose","focus_area":"this is my focus area","success_metrics":"this is how I will measure success","organization_level":"vp","bill_rate":"0","first_name":"new","last_name":"client","email":"new7408@nike.com","coachs":[],"documents":[],"sessions":[],"documentTemplates":[]},{"id":"47","coach_id":"-1","organization_id":"1","user_id":"46","start_date":"2012-01-02","sessions_allotment":"33","sessions_frequency":"Every other week","tags":"sanjose","focus_area":"this is my focus area","success_metrics":"this is how I will measure success","organization_level":"vp","bill_rate":"0","first_name":"new","last_name":"client","email":"new4059@nike.com","coachs":[],"documents":[],"sessions":[],"documentTemplates":[]},{"id":"48","coach_id":"-1","organization_id":"1","user_id":"47","start_date":"2012-01-02","sessions_allotment":"33","sessions_frequency":"Every other week","tags":"sanjose","focus_area":"this is my focus area","success_metrics":"this is how I will measure success","organization_level":"vp","bill_rate":"0","first_name":"new","last_name":"client","email":"new6306@nike.com","coachs":[],"documents":[],"sessions":[],"documentTemplates":[]},{"id":"49","coach_id":"-1","organization_id":"1","user_id":"48","start_date":"2012-01-02","sessions_allotment":"33","sessions_frequency":"Every other week","tags":"sanjose","focus_area":"this is my focus area","success_metrics":"this is how I will measure success","organization_level":"vp","bill_rate":"0","first_name":"new","last_name":"client","email":"new4884@nike.com","coachs":[],"documents":[],"sessions":[],"documentTemplates":[]},{"id":"52","coach_id":"-1","organization_id":"1","user_id":"51","start_date":"2012-01-02","sessions_allotment":"33","sessions_frequency":"Every other week","tags":"sanjose","focus_area":"this is my focus area","success_metrics":"this is how I will measure success","organization_level":"vp","bill_rate":"0","first_name":"new","last_name":"client","email":"new4885@nike.com","coachs":[],"documents":[],"sessions":[],"documentTemplates":[]},{"id":"87","coach_id":"-1","organization_id":"1","user_id":"34","start_date":"2012-06-01","sessions_allotment":"16","sessions_frequency":"3 times per month","tags":"divisionA","focus_area":"This is my focus.","success_metrics":"This is my success.","organization_level":"VP","bill_rate":"0","first_name":"new","last_name":"client","email":"jeremy@jeremystover.com","coachs":[],"documents":[],"sessions":[],"documentTemplates":[]},{"id":"88","coach_id":"-1","organization_id":"1","user_id":"35","start_date":"2012-06-01","sessions_allotment":"16","sessions_frequency":"3 times per month","tags":"divisionA","focus_area":"This is my focus.","success_metrics":"This is my success.","organization_level":"VP","bill_rate":"0","first_name":"new","last_name":"client","email":"jeremy@designed-development.com","coachs":[],"documents":[],"sessions":[],"documentTemplates":[]},{"id":"89","coach_id":"-1","organization_id":"1","user_id":"36","start_date":"2012-06-01","sessions_allotment":"16","sessions_frequency":"3 times per month","tags":"divisionA","focus_area":"This is my focus.","success_metrics":"This is my success.","organization_level":"VP","bill_rate":"0","first_name":"new","last_name":"client","email":"new9296@nike.com","coachs":[],"documents":[],"sessions":[],"documentTemplates":[]},{"id":"90","coach_id":"-1","organization_id":"1","user_id":"37","start_date":"2012-06-01","sessions_allotment":"16","sessions_frequency":"3 times per month","tags":"divisionA","focus_area":"This is my focus.","success_metrics":"This is my success.","organization_level":"VP","bill_rate":"0","first_name":"new","last_name":"client","email":"new424@nike.com","coachs":[],"documents":[],"sessions":[],"documentTemplates":[]},{"id":"91","coach_id":"-1","organization_id":"1","user_id":"38","start_date":"2012-06-01","sessions_allotment":"16","sessions_frequency":"3 times per month","tags":"divisionA","focus_area":"This is my updated focus.","success_metrics":"This is my success.","organization_level":"VP","bill_rate":"0","first_name":"new","last_name":"client","email":"new8160@nike.com","coachs":[],"documents":[],"sessions":[],"documentTemplates":[]},{"id":"92","coach_id":"-1","organization_id":"1","user_id":"39","start_date":"2012-06-01","sessions_allotment":"16","sessions_frequency":"3 times per month","tags":"divisionA","focus_area":"This is my focus.","success_metrics":"This is my success.","organization_level":"VP","bill_rate":"0","first_name":"new","last_name":"client","email":"new6638@nike.com","coachs":[],"documents":[],"sessions":[],"documentTemplates":[]},{"id":"93","coach_id":"-1","organization_id":"1","user_id":"40","start_date":"2012-06-01","sessions_allotment":"16","sessions_frequency":"3 times per month","tags":"divisionA","focus_area":"This is my focus.","success_metrics":"This is my success.","organization_level":"VP","bill_rate":"0","first_name":"new","last_name":"client","email":"new4651@nike.com","coachs":[],"documents":[],"sessions":[],"documentTemplates":[]},{"id":"101","coach_id":"-1","organization_id":"1","user_id":"51","start_date":"2012-06-01","sessions_allotment":"16","sessions_frequency":"3 times per month","tags":"divisionA","focus_area":"This is my focus.","success_metrics":"This is my success.","organization_level":"VP","bill_rate":"0","first_name":"new","last_name":"client","email":"new4885@nike.com","coachs":[],"documents":[],"sessions":[],"documentTemplates":[]},{"id":"1","coach_id":"1","organization_id":"1","user_id":"4","start_date":"2012-06-14","sessions_allotment":"16","sessions_frequency":"Weekly","tags":"SanJose","focus_area":"Becoming a better leader","success_metrics":"A new level in sales and employee engagement.","organization_level":"Executive Vice President","bill_rate":"0","first_name":"Mrs.","last_name":"Client","email":"client@nike.com","coachs":[{"id":"1","user_id":"3","schedule_url":"http:\/\/www.timetrade.com","bio":"Here is my bio...","bio_complete":"1","expertise":"","pay_rate":"0","first_name":"Jeremy","last_name":"Stover","email":"jeremy@connectedadvantage.com"}],"documents":[{"id":"78","client_id":"1","title":"008_MyCalmBeat_Gateway.xlsx","url":"\/cti\/uploads\/008_MyCalmBeat_Gateway.xlsx","documentTemplate_id":"1","documentTemplates":[{"id":"1","title":"facilisi. Sed neque. Sed","url":"http:\/\/www.mydocumentlink.com\/template","readonly":"0","confidential":"0"}]}],"sessions":[{"id":"1","client_id":"1","coach_id":"1","session_datetime":"2013-01-01 06:16:29","confidential_notes":"this is the confidential note","progress_notes":"Here is my progress","progress_notes_approved":"1","status_code":"1","duration":"40","coachs":[{"id":"1","user_id":"3","schedule_url":"http:\/\/www.timetrade.com","bio":"Here is my bio...","bio_complete":"1","expertise":"","pay_rate":"0","first_name":"Jeremy","last_name":"Stover","email":"jeremy@connectedadvantage.com"}]}],"":null},{"coachs":[{"id":"1","user_id":"3","schedule_url":"http:\/\/www.timetrade.com","bio":"Here is my bio...","bio_complete":"1","expertise":"","pay_rate":"0","first_name":"Jeremy","last_name":"Stover","email":"jeremy@connectedadvantage.com"}],"documents":[{"id":"78","client_id":"1","title":"008_MyCalmBeat_Gateway.xlsx","url":"\/cti\/uploads\/008_MyCalmBeat_Gateway.xlsx","documentTemplate_id":"1","documentTemplates":[{"id":"1","title":"facilisi. Sed neque. Sed","url":"http:\/\/www.mydocumentlink.com\/template","readonly":"0","confidential":"0"}]},{"documentTemplates":[{"id":"1","title":"facilisi. Sed neque. Sed","url":"http:\/\/www.mydocumentlink.com\/template","readonly":"0","confidential":"0"}]},{"id":"79","client_id":"1","title":"build.xml","url":"\/cti\/uploads\/build.xml","documentTemplate_id":"2","documentTemplates":[{"id":"2","title":"eu, ultrices sit","url":"http:\/\/www.mydocumentlink.com\/template","readonly":"0","confidential":"0"}]}],"sessions":[{"id":"26","client_id":"1","coach_id":"3","session_datetime":"2012-06-12 15:00:00","confidential_notes":"This note is confidential.","progress_notes":"This is my success.","progress_notes_approved":"0","status_code":"1","duration":"40"},{"id":"27","client_id":"1","coach_id":"3","session_datetime":"2012-06-12 15:00:00","confidential_notes":"This note is confidential.","progress_notes":"This is my success.","progress_notes_approved":"0","status_code":"1","duration":"40"},{"id":"28","client_id":"1","coach_id":"3","session_datetime":"2012-06-12 15:00:00","confidential_notes":"This note is confidential.","progress_notes":"This is my success.","progress_notes_approved":"0","status_code":"1","duration":"40"},{"id":"29","client_id":"1","coach_id":"3","session_datetime":"2012-06-12 15:00:00","confidential_notes":"This note is confidential.","progress_notes":"This is my success.","progress_notes_approved":"0","status_code":"1","duration":"40"},{"id":"30","client_id":"1","coach_id":"3","session_datetime":"2012-06-12 15:00:00","confidential_notes":"This note is confidential.","progress_notes":"This is my success.","progress_notes_approved":"0","status_code":"1","duration":"40"},{"id":"31","client_id":"1","coach_id":"3","session_datetime":"2012-06-12 15:00:00","confidential_notes":"This note is a confidential note.","progress_notes":"This is my success.","progress_notes_approved":"0","status_code":"1","duration":"40"},{"id":"32","client_id":"1","coach_id":"3","session_datetime":"2012-06-12 15:00:00","confidential_notes":"This note is confidential.","progress_notes":"This is my success.","progress_notes_approved":"1","status_code":"1","duration":"40"},{"id":"33","client_id":"1","coach_id":"3","session_datetime":"2012-06-12 15:00:00","confidential_notes":"This note is confidential.","progress_notes":"This is my success.","progress_notes_approved":"1","status_code":"1","duration":"40"},{"id":"34","client_id":"1","coach_id":"3","session_datetime":"2012-06-12 15:00:00","confidential_notes":"This note is s confidential.","progress_notes":"This is my success.","progress_notes_approved":"0","status_code":"1","duration":"40"},{"id":"35","client_id":"1","coach_id":"3","session_datetime":"2012-06-12 15:00:00","confidential_notes":"This note is ff confidential.","progress_notes":"This is my success.","progress_notes_approved":"1","status_code":"1","duration":"40"},{"id":"1","client_id":"1","coach_id":"1","session_datetime":"2013-01-01 06:16:29","confidential_notes":"this is the confidential note","progress_notes":"Here is my progress","progress_notes_approved":"1","status_code":"1","duration":"40","coachs":[{"id":"1","user_id":"3","schedule_url":"http:\/\/www.timetrade.com","bio":"Here is my bio...","bio_complete":"1","expertise":"","pay_rate":"0","first_name":"Jeremy","last_name":"Stover","email":"jeremy@connectedadvantage.com"}]},{"":null},{"id":"26","client_id":"1","coach_id":"3","session_datetime":"2012-06-12 15:00:00","confidential_notes":"This note is confidential.","progress_notes":"This is my success.","progress_notes_approved":"0","status_code":"1","duration":"40","coachs":[]},{"id":"27","client_id":"1","coach_id":"3","session_datetime":"2012-06-12 15:00:00","confidential_notes":"This note is confidential.","progress_notes":"This is my success.","progress_notes_approved":"0","status_code":"1","duration":"40","coachs":[]},{"id":"28","client_id":"1","coach_id":"3","session_datetime":"2012-06-12 15:00:00","confidential_notes":"This note is confidential.","progress_notes":"This is my success.","progress_notes_approved":"0","status_code":"1","duration":"40","coachs":[]},{"id":"29","client_id":"1","coach_id":"3","session_datetime":"2012-06-12 15:00:00","confidential_notes":"This note is confidential.","progress_notes":"This is my success.","progress_notes_approved":"0","status_code":"1","duration":"40","coachs":[]},{"id":"30","client_id":"1","coach_id":"3","session_datetime":"2012-06-12 15:00:00","confidential_notes":"This note is confidential.","progress_notes":"This is my success.","progress_notes_approved":"0","status_code":"1","duration":"40","coachs":[]},{"id":"31","client_id":"1","coach_id":"3","session_datetime":"2012-06-12 15:00:00","confidential_notes":"This note is a confidential note.","progress_notes":"This is my success.","progress_notes_approved":"0","status_code":"1","duration":"40","coachs":[]},{"id":"32","client_id":"1","coach_id":"3","session_datetime":"2012-06-12 15:00:00","confidential_notes":"This note is confidential.","progress_notes":"This is my success.","progress_notes_approved":"1","status_code":"1","duration":"40","coachs":[]},{"id":"33","client_id":"1","coach_id":"3","session_datetime":"2012-06-12 15:00:00","confidential_notes":"This note is confidential.","progress_notes":"This is my success.","progress_notes_approved":"1","status_code":"1","duration":"40","coachs":[]},{"id":"34","client_id":"1","coach_id":"3","session_datetime":"2012-06-12 15:00:00","confidential_notes":"This note is s confidential.","progress_notes":"This is my success.","progress_notes_approved":"0","status_code":"1","duration":"40","coachs":[]}],"documentTemplates":[{"id":"2","title":"eu, ultrices sit","url":"http:\/\/www.mydocumentlink.com\/template","readonly":"0","confidential":"0"}]},{"id":"45","coach_id":"-1","organization_id":"1","user_id":"44","start_date":"2012-01-02","sessions_allotment":"33","sessions_frequency":"Every other week","tags":"sanjose","focus_area":"this is my focus area","success_metrics":"this is how I will measure success","organization_level":"vp","bill_rate":"0","first_name":"new","last_name":"client","email":"new4739@nike.com","coachs":[],"documents":[],"sessions":[],"documentTemplates":[]},{"id":"46","coach_id":"-1","organization_id":"1","user_id":"45","start_date":"2012-01-02","sessions_allotment":"33","sessions_frequency":"Every other week","tags":"sanjose","focus_area":"this is my focus area","success_metrics":"this is how I will measure success","organization_level":"vp","bill_rate":"0","first_name":"new","last_name":"client","email":"new7408@nike.com","coachs":[],"documents":[],"sessions":[],"documentTemplates":[]},{"id":"47","coach_id":"-1","organization_id":"1","user_id":"46","start_date":"2012-01-02","sessions_allotment":"33","sessions_frequency":"Every other week","tags":"sanjose","focus_area":"this is my focus area","success_metrics":"this is how I will measure success","organization_level":"vp","bill_rate":"0","first_name":"new","last_name":"client","email":"new4059@nike.com","coachs":[],"documents":[],"sessions":[],"documentTemplates":[]},{"id":"48","coach_id":"-1","organization_id":"1","user_id":"47","start_date":"2012-01-02","sessions_allotment":"33","sessions_frequency":"Every other week","tags":"sanjose","focus_area":"this is my focus area","success_metrics":"this is how I will measure success","organization_level":"vp","bill_rate":"0","first_name":"new","last_name":"client","email":"new6306@nike.com","coachs":[],"documents":[],"sessions":[],"documentTemplates":[]},{"id":"49","coach_id":"-1","organization_id":"1","user_id":"48","start_date":"2012-01-02","sessions_allotment":"33","sessions_frequency":"Every other week","tags":"sanjose","focus_area":"this is my focus area","success_metrics":"this is how I will measure success","organization_level":"vp","bill_rate":"0","first_name":"new","last_name":"client","email":"new4884@nike.com","coachs":[],"documents":[],"sessions":[],"documentTemplates":[]},{"id":"52","coach_id":"-1","organization_id":"1","user_id":"51","start_date":"2012-01-02","sessions_allotment":"33","sessions_frequency":"Every other week","tags":"sanjose","focus_area":"this is my focus area","success_metrics":"this is how I will measure success","organization_level":"vp","bill_rate":"0","first_name":"new","last_name":"client","email":"new4885@nike.com","coachs":[],"documents":[],"sessions":[],"documentTemplates":[]},{"id":"87","coach_id":"-1","organization_id":"1","user_id":"34","start_date":"2012-06-01","sessions_allotment":"16","sessions_frequency":"3 times per month","tags":"divisionA","focus_area":"This is my focus.","success_metrics":"This is my success.","organization_level":"VP","bill_rate":"0","first_name":"new","last_name":"client","email":"jeremy@jeremystover.com","coachs":[],"documents":[],"sessions":[],"documentTemplates":[]},{"id":"88","coach_id":"-1","organization_id":"1","user_id":"35","start_date":"2012-06-01","sessions_allotment":"16","sessions_frequency":"3 times per month","tags":"divisionA","focus_area":"This is my focus.","success_metrics":"This is my success.","organization_level":"VP","bill_rate":"0","first_name":"new","last_name":"client","email":"jeremy@designed-development.com","coachs":[],"documents":[],"sessions":[],"documentTemplates":[]},{"id":"89","coach_id":"-1","organization_id":"1","user_id":"36","start_date":"2012-06-01","sessions_allotment":"16","sessions_frequency":"3 times per month","tags":"divisionA","focus_area":"This is my focus.","success_metrics":"This is my success.","organization_level":"VP","bill_rate":"0","first_name":"new","last_name":"client","email":"new9296@nike.com","coachs":[],"documents":[],"sessions":[],"documentTemplates":[]},{"id":"90","coach_id":"-1","organization_id":"1","user_id":"37","start_date":"2012-06-01","sessions_allotment":"16","sessions_frequency":"3 times per month","tags":"divisionA","focus_area":"This is my focus.","success_metrics":"This is my success.","organization_level":"VP","bill_rate":"0","first_name":"new","last_name":"client","email":"new424@nike.com","coachs":[],"documents":[],"sessions":[],"documentTemplates":[]},{"id":"91","coach_id":"-1","organization_id":"1","user_id":"38","start_date":"2012-06-01","sessions_allotment":"16","sessions_frequency":"3 times per month","tags":"divisionA","focus_area":"This is my updated focus.","success_metrics":"This is my success.","organization_level":"VP","bill_rate":"0","first_name":"new","last_name":"client","email":"new8160@nike.com","coachs":[],"documents":[],"sessions":[],"documentTemplates":[]},{"id":"92","coach_id":"-1","organization_id":"1","user_id":"39","start_date":"2012-06-01","sessions_allotment":"16","sessions_frequency":"3 times per month","tags":"divisionA","focus_area":"This is my focus.","success_metrics":"This is my success.","organization_level":"VP","bill_rate":"0","first_name":"new","last_name":"client","email":"new6638@nike.com","coachs":[],"documents":[],"sessions":[],"documentTemplates":[]},{"id":"93","coach_id":"-1","organization_id":"1","user_id":"40","start_date":"2012-06-01","sessions_allotment":"16","sessions_frequency":"3 times per month","tags":"divisionA","focus_area":"This is my focus.","success_metrics":"This is my success.","organization_level":"VP","bill_rate":"0","first_name":"new","last_name":"client","email":"new4651@nike.com","coachs":[],"documents":[],"sessions":[],"documentTemplates":[]}]},{"id":"2","organization_name":"Google","user_id":"0","addr_street":"4667 Mission Street","addr_city":"San Francisco","addr_state":"CA","addr_zip":"94110","notes":"some notes on this company","budget":"50000","first_name":null,"last_name":null,"email":null,"coachs":[],"documentTemplates":[{"id":"3","title":"ligula. Aenean gravida nunc sed","url":"http:\/\/www.mydocumentlink.com\/template","readonly":"0","confidential":"0"}],"clients":[]},{"id":"3","organization_name":"Borland","user_id":"0","addr_street":"4667 Mission Street","addr_city":"San Francisco","addr_state":"CA","addr_zip":"94110","notes":"some notes on this company","budget":"50000","first_name":null,"last_name":null,"email":null,"coachs":[],"documentTemplates":[],"clients":[]},{"id":"4","organization_name":"Adobe","user_id":"0","addr_street":"4667 Mission Street","addr_city":"San Francisco","addr_state":"CA","addr_zip":"94110","notes":"some notes on this company","budget":"50000","first_name":null,"last_name":null,"email":null,"coachs":[],"documentTemplates":[],"clients":[]},{"id":"5","organization_name":"Chami","user_id":"0","addr_street":"4667 Mission Street","addr_city":"San Francisco","addr_state":"CA","addr_zip":"94110","notes":"some notes on this company","budget":"50000","first_name":null,"last_name":null,"email":null,"coachs":[],"documentTemplates":[],"clients":[]},{"id":"6","organization_name":"Lavasoft","user_id":"0","addr_street":"4667 Mission Street","addr_city":"San Francisco","addr_state":"CA","addr_zip":"94110","notes":"some notes on this company","budget":"50000","first_name":null,"last_name":null,"email":null,"coachs":[],"documentTemplates":[],"clients":[]},{"id":"7","organization_name":"Macromedia","user_id":"0","addr_street":"4667 Mission Street","addr_city":"San Francisco","addr_state":"CA","addr_zip":"94110","notes":"some notes on this company","budget":"50000","first_name":null,"last_name":null,"email":null,"coachs":[],"documentTemplates":[],"clients":[]}]};
var dataCallback;
var dataSet = false;
var dataSubset = "";

//universal
function login(user,pw,callback) {
    dataCallback = callback;
    api_call('POST',SERVER_URI + "/login",{"username":user,"password":pw},loginCallback);
}
function loginCallback(code, data) {
    userType = data.code;
    dataCallback(code, data);
}
function logout(callback) {
    api_call('POST',SERVER_URI + "/logout",{},callback);
}
function forgot(email, callback) {
    api_call('POST',SERVER_URI + "/forgot",{"username":email},callback);
}
function updateLogin(registration_code, user, pw, callback) {
    api_call('POST',SERVER_URI + "/update",{"registration_code":registration_code,"username":user,"password":pw},callback);
}

//get data object ------------------------------------------------------------------------------------------------------
function getData(callback) {
    mql_call(MQL_URI, query, function(success,data) {
        thedata = data;
        dataSet = true;
        callback(success, data);
    });
}

function getClients(callback) {
    if (userType==USER_TYPE.ORGANIZATION_SPONSOR) getClientsForOrganization("", callback); else getClientsForCoach("", callback);
}
function getClientsForOrganization(organizationId, callback) {
    api_call('GET',SERVER_URI + "/organization_clients/" + organizationId,{},callback);
}

function getClientsForCoach(coachId, callback) {
   api_call('GET',SERVER_URI + "/coach_clients/" + coachId,{},callback);
}


function getSessions(callback) {
    if (userType==USER_TYPE.CLIENT) getSessionsForClient("", callback); else if (userType==USER_TYPE.ORGANIZATION_SPONSOR) getSessionsForOrganization("", callback); else getSessionsForCoach("", callback);
}

function getSessionsForOrganization(organizationId, callback) {
    api_call('GET',SERVER_URI + "/organization_sessions/" + organizationId,{},callback);
}

function getSessionsForClient(clientId, callback) {
    /* if (userType==USER_TYPE.CLIENT) {
        if (!dataSet) {
            getData(function(success, data) {
                thedata = data;
                dataSet = true;
                callback(1, thedata.clients[0].sessions);
            } );
        } else {
            callback(1, thedata.clients[0].sessions);
        }
        return;
    } */
    api_call('GET',SERVER_URI + "/client_sessions/" + clientId,{},callback);
}

function getSessionsForCoach(coachId, callback) {
    /* if (userType==USER_TYPE.COACH) {
        if (!dataSet) {
            getData(function(success, data) {
                thedata = data;
                dataSet = true;
                callback(1, thedata.coachs[0].sessions);
            } );
        } else {
            callback(1, thedata.coachs[0].sessions);
        }
        return;
    } */
    api_call('GET',SERVER_URI + "/coach_sessions/" + coachId,{},callback);
}

function getOrganizations(callback) {
    getOrganizationsForCoach("", callback);
}

function getOrganizationsForCoach(coachId, callback) {
    /* if (userType==USER_TYPE.ORGANIZATION_SPONSOR) {
        if (!dataSet) {
            getData(function(success, data) {
                thedata = data;
                dataSet = true;
                callback(1, thedata.organizations[0]);
            });
        } else {
            callback(1, thedata.organizations[0]);
        }
        return;
    } else if (userType==USER_TYPE.CLIENT) {
        if (!dataSet) {
            getData(function(success, data) {
                thedata = data;
                dataSet = true;
                callback(1, thedata.clients[0].organizations[0]);
            } );
        } else {
            callback(1, thedata.clients[0].organizations[0]);
        }
        return;
    } */
    api_call('GET',SERVER_URI + "/organizations/" + coachId,{},callback);

}

function getCoachesForOrganization(organizationId, callback) {
   /* if (userType==USER_TYPE.COACH) {
        if (!dataSet) {
            getData(function(success, data) {
                thedata = data;
                dataSet = true;
                callback(1, thedata.coachs[0]);
            } );
        } else {
            callback(1, thedata.coachs[0]);
        }
        return;
    } else if (userType==USER_TYPE.CLIENT) {
        if (!dataSet) {
            getData(function(success, data) {
                thedata = data;
                dataSet = true;
                callback(1, thedata.clients[0].coachs[0]);
            } );
        } else {
            callback(1, thedata.clients[0].coachs[0]);
        }
        return;
    } */
    api_call('GET',SERVER_URI + "/coachs/" + organizationId,{},callback);
}

function getCoaches(callback) {
    getCoachesForOrganization("", callback);
}

function getDocuments(callback) {
    getDocumentsForClient("", callback);
}
function getDocumentsForClient(clientId, callback) {
    /* if (userType==USER_TYPE.CLIENT) {
        callback(1, thedata.clients[0].documents);
        return;
    } */
    api_call('GET',SERVER_URI + "/documents/" + clientId,{},callback);
}

function getDocumentTemplates(callback) {
    getDocumentTemplatesForOrganization("", callback);
}
function getDocumentTemplatesForOrganization(organizationId, callback) {
    api_call('GET',SERVER_URI + "/documentTemplates/" + organizationId,{},callback);
}



//----------------------------------------------------------------------------------------------------------------------

function addClient(values, callback) {
    values["userType"]=USER_TYPE.CLIENT;
    api_call("PUT", SERVER_URI +'/clients',values,callback);
}
function addOrganization(values, callback){
    values["userType"]=USER_TYPE.ORGANIZATION_SPONSOR;
    api_call("PUT", SERVER_URI +'/organizations',values,callback);
}
function addCoach(values, callback) {
    values["userType"]=USER_TYPE.COACH;
    api_call("PUT", SERVER_URI +'/coachs',values,callback);
}

function addSession(values, callback) {
    api_call("PUT", SERVER_URI +'/sessions',values,callback);
}

function updateClient(clientId, values, callback) {
    api_call("POST", SERVER_URI +'/clients/' + clientId,values,callback);
}

function updateOrganization(organizationId, values, callback){
    api_call("POST", SERVER_URI +'/organizations/' + organizationId,values,callback);
}

function updateSession(sessionId, values, callback) {
    api_call("POST", SERVER_URI +'/sessions/' + sessionId,values,callback);
}
function updateCoach(coachId, values, callback) {
    api_call("POST", SERVER_URI +'/coachs/' + coachId,values,callback);
}

function deleteClient(clientId, callback) {
    api_call("DELETE", SERVER_URI +'/clients/' + clientId,null,callback);
}
function deleteCoach(coachId, callback) {
    api_call("DELETE", SERVER_URI +'/coachs/' + coachId,null,callback);
}
function deleteOrganization(organizationId, callback) {
    api_call("DELETE", SERVER_URI +'/organizations/' + organizationId,null,callback);
}
function deleteSession(sessionId, callback) {
    api_call("DELETE", SERVER_URI +'/sessions/' + sessionId,null,callback);
}
function deleteDocument(documentId, callback) {
    api_call("DELETE", SERVER_URI +'/documents/' + documentId,null,callback);
}

function deleteDocumentTemplate(documentTemplateId, callback) {
    api_call("DELETE", SERVER_URI +'/documentTemplates/' + documentTemplateId,null,callback);
}

function linkCoachToOrganization(coachId, organizationId, callback) {
    var values = Object();
    values["coach_id"] = coachId;
    values["organization_id"] = organizationId;
    api_call("PUT", SERVER_URI +'/link_organizations_coachs/',values,callback);
}

function unlinkCoachToOrganization(coachId, organizationId, callback) {
    api_call("DELETE", SERVER_URI +'/link_organizations_coachs/' + organizationId + "/" + coachId,null,callback);
}

function linkDocumentToOrganization(documentTemplateId, organizationId, callback) {
    var values = Object();
    values["documentTemplate_id"] = documentTemplateId;
    values["organization_id"] = organizationId;
    api_call("PUT", SERVER_URI +'/link_organizations_documentTemplates/',values,callback);
}

function unlinkDocumentToOrganization(documentTemplateId, organizationId, callback) {
    api_call("DELETE", SERVER_URI +'/link_organizations_documentTemplates/' + organizationId + "/" + documentTemplateId,null,callback);
}

function getAccountingReport(startdate, enddate, organizationId, tags, callback) {
   api_call("GET", SERVER_URI +'/report/' + startdate + "/"  + enddate  + "/" + organizationId + "/" + tags,null,callback);
}

function getProgressReport(clientId, startdate, enddate, callback) {
    document.location.href= SERVER_URI +'/report/' + startdate + "/" + enddate + "/" + clientId;
    callback(1,null);
}

function getDocumentTemplates(callback) {
    api_call("GET", SERVER_URI + "/documentTemplates", null, callback);
}

function api_call(type, url, data, callback) {
    console.log("Sending  to " + url);
    //console.log(data);
    if (data==null || data==undefined) data = new Object();
    data["session_id"] = session_id;
    $.ajax({
        type: type,
        url: url,
        data: data,
        crossDomain: true,
        success: function(data) {
            if (data!=undefined && data.session_id!=undefined) session_id = data.session_id;
            //console.log(data);
            if (type=="DELETE")
                callback(1,data);
            else if (type=="GET")
                callback(1,data);
            else if (data.success=='true')
                callback(1,data);
            else
                callback(0,data);
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log(textStatus);
            console.log(jqXHR);
            console.log(errorThrown);
            callback(-1,textStatus);
        },
        dataType: "json"
    });
}

function mql_call(url, query, callback) {
    console.log("Sending  to " + url);
    if (data==null || data==undefined) data = new Object();
    data["session_id"] = session_id;
    var queryEnvelope = "{"    +
        "\"query\":" + query   +
        "}";
    url = url + "?query=" + encodeURIComponent(queryEnvelope);

    $.ajax({
        type: 'GET',
        url: url,
        data: data,
        crossDomain: true,
        success: function(data) {
            if (data!=undefined && data.session_id!=undefined) session_id = data.session_id;
            callback(1,data.result);
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log(textStatus);
            console.log(jqXHR);
            console.log(errorThrown);
            callback(-1,textStatus);
        },
        dataType: "json"
    });
}
