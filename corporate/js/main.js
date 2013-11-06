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

var USER_TYPE = {
    ACCOUNT_MANAGER: 1,
    ORGANIZATION_SPONSOR : 2,
    COACH: 3,
    CLIENT : 4,
    ACCOUNTING: 5
};
var SESSION_STATUS = {
    UPCOMING: 0,
    COMPLETE : 1,
    NOSHOW: 2,
    CANCELLED : 3,
    OTHER: 4
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
    pw=hex_sha512(pw);
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
    pw = hex_sha512(pw);
    api_call('POST',SERVER_URI + "/update",{"registration_code":registration_code,"username":user,"password":pw},callback);
}

//get data object
function getData(callback) {
    dataCallback = callback;
    api_call('GET',SERVER_URI + "/data",{},function (success, data) {
        thedata = data;
        dataSet =true;
        dataCallback(success, data);
    });
}

function getClients(callback) {
    if (userType==USER_TYPE.ORGANIZATION_SPONSOR) getClientsForOrganization("", callback); else getClientsForCoach("", callback);
}
function getClientsForOrganization(organizationId, callback) {
   /* if (userType==USER_TYPE.ORGANIZATION_SPONSOR) {
        if (!dataSet) {
            getData(function(success, data) {
                thedata = data;
                dataSet = true;
                callback(1, thedata.organizations[0].clients);
            } );
        } else {
            callback(1, thedata.organizations[0].clients);
        }
        return;
    } */
    api_call('GET',SERVER_URI + "/organization_clients/" + organizationId,{},callback);
}

function getClientsForCoach(coachId, callback) {
    /* if (userType==USER_TYPE.COACH) {
        if (!dataSet) {
            getData(function(success, data) {
                thedata = data;
                dataSet = true;
                callback(1, thedata.coachs[0].clients);
            } );
        } else {
            callback(1, thedata.coachs[0].clients);
        }
        return;
    } else if (userType==USER_TYPE.CLIENT) {
        if (!dataSet) {
            getData(function(success, data) {
                thedata = data;
                dataSet = true;
                callback(1, thedata.clients[0]);
            } );
        } else {
            callback(1, thedata.client[0]);
        }
        return;
    } */
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

function getStats(callback) {
    if (userType==USER_TYPE.ORGANIZATION_SPONSOR) getStatsForOrganization("", callback);
}
function getStatsForOrganization(organizationId, callback) {
    api_call('GET',SERVER_URI + "/organization_stats/" + organizationId,{},callback);
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

function getOrganizationsForDocumentTemplate(documentTemplateId, callback) {
    api_call('GET',SERVER_URI + "/documentTemplate_organizations/" + documentTemplateId,{},callback);
}

function getDocumentTemplatesForOrganization(organizationId, callback) {
    api_call('GET',SERVER_URI + "/documentTemplates/" + organizationId,{},callback);
}


function addClient(values, callback) {
    values["userType"]=USER_TYPE.CLIENT;
    if (typeof values["coach_id"] == 'undefined') values["coach_id"] = "null";
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
function updateDocumentTemplate(documentTemplateId, values, callback) {
    api_call("POST", SERVER_URI + "/documentTemplates/" + documentTemplateId, values, callback);
}
function updateDocument(documentId, values, callback) {
    api_call("POST", SERVER_URI + "/documents/" + documentId, values, callback);
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


function linkCoachToOrganizations(coachId, organizationIds, callback) {
    var values = Object();
    values["coach_id"] = coachId;
    values["organization_ids"] = organizationIds.join("|");
    api_call("POST", SERVER_URI +'/link_organizations_to_coach/',values,callback);
}

function linkDocumentToOrganizations(documentTemplateId, organizationIds, callback) {
    var values = Object();
    values["documentTemplate_id"] = documentTemplateId;
    values["organization_ids"] = organizationIds.join("|");
    api_call("POST", SERVER_URI +'/link_organizations_to_documentTemplate/',values,callback);
}



function unlinkDocumentToOrganization(documentTemplateId, organizationId, callback) {
    api_call("DELETE", SERVER_URI +'/link_organizations_documentTemplates/' + organizationId + "/" + documentTemplateId,null,callback);
}

function getAccountingReport(startdate, enddate, organizationId, tags, callback) {
   api_call("GET", SERVER_URI +'/report/' + startdate + "/"  + enddate  + "/" + organizationId + "/" + tags,null,callback);
}

function downloadAccountingReport(startdate, enddate, organizationId, tags, callback) {
    api_call("GET", SERVER_URI +'/report/' + startdate + "/"  + enddate  + "/" + organizationId + "/" + tags + "/csv",null,callback);
}

function getProgressReport(clientId, startdate, enddate, callback) {
    document.location.href= SERVER_URI +'/report/' + startdate + "/" + enddate + "/" + clientId;
    callback(1,null);
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
var hexcase=0,b64pad="";function hex_sha512(c){return rstr2hex(rstr_sha512(str2rstr_utf8(c)))}function b64_sha512(c){return rstr2b64(rstr_sha512(str2rstr_utf8(c)))}function any_sha512(c,b){return rstr2any(rstr_sha512(str2rstr_utf8(c)),b)}function hex_hmac_sha512(c,b){return rstr2hex(rstr_hmac_sha512(str2rstr_utf8(c),str2rstr_utf8(b)))}function b64_hmac_sha512(c,b){return rstr2b64(rstr_hmac_sha512(str2rstr_utf8(c),str2rstr_utf8(b)))}
function any_hmac_sha512(c,b,a){return rstr2any(rstr_hmac_sha512(str2rstr_utf8(c),str2rstr_utf8(b)),a)}function sha512_vm_test(){return"ddaf35a193617abacc417349ae20413112e6fa4e89a97ea20a9eeee64b55d39a2192992a274fc1a836ba3c23a3feebbd454d4423643ce80e2a9ac94fa54ca49f"==hex_sha512("abc").toLowerCase()}function rstr_sha512(c){return binb2rstr(binb_sha512(rstr2binb(c),8*c.length))}
function rstr_hmac_sha512(c,b){var a=rstr2binb(c);32<a.length&&(a=binb_sha512(a,8*c.length));for(var d=Array(32),e=Array(32),f=0;32>f;f++)d[f]=a[f]^909522486,e[f]=a[f]^1549556828;a=binb_sha512(d.concat(rstr2binb(b)),1024+8*b.length);return binb2rstr(binb_sha512(e.concat(a),1536))}function rstr2hex(c){try{hexcase}catch(b){hexcase=0}for(var a=hexcase?"0123456789ABCDEF":"0123456789abcdef",d="",e,f=0;f<c.length;f++)e=c.charCodeAt(f),d+=a.charAt(e>>>4&15)+a.charAt(e&15);return d}
function rstr2b64(c){try{b64pad}catch(b){b64pad=""}for(var a="",d=c.length,e=0;e<d;e+=3)for(var f=c.charCodeAt(e)<<16|(e+1<d?c.charCodeAt(e+1)<<8:0)|(e+2<d?c.charCodeAt(e+2):0),g=0;4>g;g++)a=8*e+6*g>8*c.length?a+b64pad:a+"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/".charAt(f>>>6*(3-g)&63);return a}
function rstr2any(c,b){var a=b.length,d,e,f,g,i,l=Array(Math.ceil(c.length/2));for(d=0;d<l.length;d++)l[d]=c.charCodeAt(2*d)<<8|c.charCodeAt(2*d+1);var k=Math.ceil(8*c.length/(Math.log(b.length)/Math.log(2))),q=Array(k);for(e=0;e<k;e++){i=[];for(d=g=0;d<l.length;d++)if(g=(g<<16)+l[d],f=Math.floor(g/a),g-=f*a,0<i.length||0<f)i[i.length]=f;q[e]=g;l=i}a="";for(d=q.length-1;0<=d;d--)a+=b.charAt(q[d]);return a}
function str2rstr_utf8(c){for(var b="",a=-1,d,e;++a<c.length;)d=c.charCodeAt(a),e=a+1<c.length?c.charCodeAt(a+1):0,55296<=d&&(56319>=d&&56320<=e&&57343>=e)&&(d=65536+((d&1023)<<10)+(e&1023),a++),127>=d?b+=String.fromCharCode(d):2047>=d?b+=String.fromCharCode(192|d>>>6&31,128|d&63):65535>=d?b+=String.fromCharCode(224|d>>>12&15,128|d>>>6&63,128|d&63):2097151>=d&&(b+=String.fromCharCode(240|d>>>18&7,128|d>>>12&63,128|d>>>6&63,128|d&63));return b}
function str2rstr_utf16le(c){for(var b="",a=0;a<c.length;a++)b+=String.fromCharCode(c.charCodeAt(a)&255,c.charCodeAt(a)>>>8&255);return b}function str2rstr_utf16be(c){for(var b="",a=0;a<c.length;a++)b+=String.fromCharCode(c.charCodeAt(a)>>>8&255,c.charCodeAt(a)&255);return b}function rstr2binb(c){for(var b=Array(c.length>>2),a=0;a<b.length;a++)b[a]=0;for(a=0;a<8*c.length;a+=8)b[a>>5]|=(c.charCodeAt(a/8)&255)<<24-a%32;return b}
function binb2rstr(c){for(var b="",a=0;a<32*c.length;a+=8)b+=String.fromCharCode(c[a>>5]>>>24-a%32&255);return b}var sha512_k;
function binb_sha512(c,b){void 0==sha512_k&&(sha512_k=[new int64(1116352408,-685199838),new int64(1899447441,602891725),new int64(-1245643825,-330482897),new int64(-373957723,-2121671748),new int64(961987163,-213338824),new int64(1508970993,-1241133031),new int64(-1841331548,-1357295717),new int64(-1424204075,-630357736),new int64(-670586216,-1560083902),new int64(310598401,1164996542),new int64(607225278,1323610764),new int64(1426881987,-704662302),new int64(1925078388,-226784913),new int64(-2132889090,
    991336113),new int64(-1680079193,633803317),new int64(-1046744716,-815192428),new int64(-459576895,-1628353838),new int64(-272742522,944711139),new int64(264347078,-1953704523),new int64(604807628,2007800933),new int64(770255983,1495990901),new int64(1249150122,1856431235),new int64(1555081692,-1119749164),new int64(1996064986,-2096016459),new int64(-1740746414,-295247957),new int64(-1473132947,766784016),new int64(-1341970488,-1728372417),new int64(-1084653625,-1091629340),new int64(-958395405,1034457026),
    new int64(-710438585,-1828018395),new int64(113926993,-536640913),new int64(338241895,168717936),new int64(666307205,1188179964),new int64(773529912,1546045734),new int64(1294757372,1522805485),new int64(1396182291,-1651133473),new int64(1695183700,-1951439906),new int64(1986661051,1014477480),new int64(-2117940946,1206759142),new int64(-1838011259,344077627),new int64(-1564481375,1290863460),new int64(-1474664885,-1136513023),new int64(-1035236496,-789014639),new int64(-949202525,106217008),new int64(-778901479,
        -688958952),new int64(-694614492,1432725776),new int64(-200395387,1467031594),new int64(275423344,851169720),new int64(430227734,-1194143544),new int64(506948616,1363258195),new int64(659060556,-544281703),new int64(883997877,-509917016),new int64(958139571,-976659869),new int64(1322822218,-482243893),new int64(1537002063,2003034995),new int64(1747873779,-692930397),new int64(1955562222,1575990012),new int64(2024104815,1125592928),new int64(-2067236844,-1578062990),new int64(-1933114872,442776044),
    new int64(-1866530822,593698344),new int64(-1538233109,-561857047),new int64(-1090935817,-1295615723),new int64(-965641998,-479046869),new int64(-903397682,-366583396),new int64(-779700025,566280711),new int64(-354779690,-840897762),new int64(-176337025,-294727304),new int64(116418474,1914138554),new int64(174292421,-1563912026),new int64(289380356,-1090974290),new int64(460393269,320620315),new int64(685471733,587496836),new int64(852142971,1086792851),new int64(1017036298,365543100),new int64(1126000580,
        -1676669620),new int64(1288033470,-885112138),new int64(1501505948,-60457430),new int64(1607167915,987167468),new int64(1816402316,1246189591)]);var a=[new int64(1779033703,-205731576),new int64(-1150833019,-2067093701),new int64(1013904242,-23791573),new int64(-1521486534,1595750129),new int64(1359893119,-1377402159),new int64(-1694144372,725511199),new int64(528734635,-79577749),new int64(1541459225,327033209)],d=new int64(0,0),e=new int64(0,0),f=new int64(0,0),g=new int64(0,0),i=new int64(0,0),
    l=new int64(0,0),k=new int64(0,0),q=new int64(0,0),r=new int64(0,0),u=new int64(0,0),s=new int64(0,0),t=new int64(0,0),v=new int64(0,0),w=new int64(0,0),n=new int64(0,0),o=new int64(0,0),p=new int64(0,0),h,j,m=Array(80);for(j=0;80>j;j++)m[j]=new int64(0,0);c[b>>5]|=128<<24-(b&31);c[(b+128>>10<<5)+31]=b;for(j=0;j<c.length;j+=32){int64copy(f,a[0]);int64copy(g,a[1]);int64copy(i,a[2]);int64copy(l,a[3]);int64copy(k,a[4]);int64copy(q,a[5]);int64copy(r,a[6]);int64copy(u,a[7]);for(h=0;16>h;h++)m[h].h=c[j+
    2*h],m[h].l=c[j+2*h+1];for(h=16;80>h;h++)int64rrot(n,m[h-2],19),int64revrrot(o,m[h-2],29),int64shr(p,m[h-2],6),t.l=n.l^o.l^p.l,t.h=n.h^o.h^p.h,int64rrot(n,m[h-15],1),int64rrot(o,m[h-15],8),int64shr(p,m[h-15],7),s.l=n.l^o.l^p.l,s.h=n.h^o.h^p.h,int64add4(m[h],t,m[h-7],s,m[h-16]);for(h=0;80>h;h++)v.l=k.l&q.l^~k.l&r.l,v.h=k.h&q.h^~k.h&r.h,int64rrot(n,k,14),int64rrot(o,k,18),int64revrrot(p,k,9),t.l=n.l^o.l^p.l,t.h=n.h^o.h^p.h,int64rrot(n,f,28),int64revrrot(o,f,2),int64revrrot(p,f,7),s.l=n.l^o.l^p.l,s.h=
    n.h^o.h^p.h,w.l=f.l&g.l^f.l&i.l^g.l&i.l,w.h=f.h&g.h^f.h&i.h^g.h&i.h,int64add5(d,u,t,v,sha512_k[h],m[h]),int64add(e,s,w),int64copy(u,r),int64copy(r,q),int64copy(q,k),int64add(k,l,d),int64copy(l,i),int64copy(i,g),int64copy(g,f),int64add(f,d,e);int64add(a[0],a[0],f);int64add(a[1],a[1],g);int64add(a[2],a[2],i);int64add(a[3],a[3],l);int64add(a[4],a[4],k);int64add(a[5],a[5],q);int64add(a[6],a[6],r);int64add(a[7],a[7],u)}d=Array(16);for(j=0;8>j;j++)d[2*j]=a[j].h,d[2*j+1]=a[j].l;return d}
function int64(c,b){this.h=c;this.l=b}function int64copy(c,b){c.h=b.h;c.l=b.l}function int64rrot(c,b,a){c.l=b.l>>>a|b.h<<32-a;c.h=b.h>>>a|b.l<<32-a}function int64revrrot(c,b,a){c.l=b.h>>>a|b.l<<32-a;c.h=b.l>>>a|b.h<<32-a}function int64shr(c,b,a){c.l=b.l>>>a|b.h<<32-a;c.h=b.h>>>a}function int64add(c,b,a){var d=(b.l&65535)+(a.l&65535),e=(b.l>>>16)+(a.l>>>16)+(d>>>16),f=(b.h&65535)+(a.h&65535)+(e>>>16),b=(b.h>>>16)+(a.h>>>16)+(f>>>16);c.l=d&65535|e<<16;c.h=f&65535|b<<16}
function int64add4(c,b,a,d,e){var f=(b.l&65535)+(a.l&65535)+(d.l&65535)+(e.l&65535),g=(b.l>>>16)+(a.l>>>16)+(d.l>>>16)+(e.l>>>16)+(f>>>16),i=(b.h&65535)+(a.h&65535)+(d.h&65535)+(e.h&65535)+(g>>>16),b=(b.h>>>16)+(a.h>>>16)+(d.h>>>16)+(e.h>>>16)+(i>>>16);c.l=f&65535|g<<16;c.h=i&65535|b<<16}
function int64add5(c,b,a,d,e,f){var g=(b.l&65535)+(a.l&65535)+(d.l&65535)+(e.l&65535)+(f.l&65535),i=(b.l>>>16)+(a.l>>>16)+(d.l>>>16)+(e.l>>>16)+(f.l>>>16)+(g>>>16),l=(b.h&65535)+(a.h&65535)+(d.h&65535)+(e.h&65535)+(f.h&65535)+(i>>>16),b=(b.h>>>16)+(a.h>>>16)+(d.h>>>16)+(e.h>>>16)+(f.h>>>16)+(l>>>16);c.l=g&65535|i<<16;c.h=l&65535|b<<16};
