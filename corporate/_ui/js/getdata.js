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

//required for IE compatibility
jQuery.support.cors = true;

var debug = true;

var _isIE;
var _isIESet = false;
var isIE = (function()
{
    if (_isIESet) return _isIE;
    var div = document.createElement('div');
    div.innerHTML = '<!--[if IE]><i></i><![endif]-->';
    _isIE = (div.getElementsByTagName('i').length === 1);
    _isIESet = true;
    return _isIE;
}());

//TODO: make this switchable between environments so it will work where ever it is deployed instead of needing to be coded here...  Check window.location.
//Dev
var SERVER_URI = '/coachingportal/api';
//var SERVER_URI = '/corporate_sandbox/api';

//Local
// var SERVER_URI = 'http://www.thecoaches.com/coachingportal/api';

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
    LATE_CANCEL: 4,
    OTHER: 5
};

var session_id="";
var userType = 0;
var dataCallback;
var dataSet = false;
var dataSubset = "";
var currentUser = "";

//universal
function sendErrors(errStr, callback) {
//api_call('POST', SERVER_URI + "/testNotAuthorized", {}, callback);
    api_call('POST', SERVER_URI + "/report_errors", {"errors": errStr }, callback);
}

function login(user,pw,callback) {
    $.ajaxSetup({ cache: false});
   // alert(user + "=" + pw);
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
    //pw = hex_sha512(pw);
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
function getLinkedOrganizationForCoach(coachId, callback) {
    api_call('GET',SERVER_URI + "/coach_organizations/" + coachId,{},callback);
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


function addClient() { //values, optional sendemail=true, callback) {
    if (arguments.length!=2 && arguments.length!=4) {
        throw "Invalid arguments.  Must send, values, callback or values, sendemail, sendnotification, callback.";
        return;
    }
    var values = arguments[0];
    var callback;
    if (arguments.length==4) {
        values["sendemail"] = arguments[1];
        values["sendnotification"] = arguments[2];
        callback = arguments[3];

    } else {
        values["sendemail"] = false;
        values["sendnotification"] = false;
        callback = arguments[1];
    }

    values["userType"]=USER_TYPE.CLIENT;
    if (typeof values["coach_id"] == 'undefined') values["coach_id"] = "null";
    api_call("PUT", SERVER_URI +'/clients',values,callback);
}
function addOrganization() { // values, optional sendemail=true, callback){
    if (arguments.length!=2 && arguments.length!=3) {
        throw "Invalid arguments.  Must send, values, callback or values, sendemail, callback.";
        return;
    }
    var values = arguments[0];
    var callback;
    if (arguments.length==3) {
        values["sendemail"] = arguments[1];
        callback = arguments[2];
    } else {
        values["sendemail"] = true;
        callback = arguments[1];
    }

    values["userType"]=USER_TYPE.ORGANIZATION_SPONSOR;
    api_call("PUT", SERVER_URI +'/organizations',values,callback);
}
function addCoach() { // values, optional sendemail=true, callback){
    if (arguments.length!=2 && arguments.length!=3) {
        throw "Invalid arguments.  Must send, values, callback or values, sendemail, callback.";
        return;
    }
    var values = arguments[0];
    var callback;
    if (arguments.length==3) {
        values["sendemail"] = arguments[1];
        callback = arguments[2];
    } else  {
        values["sendemail"] = true;
        callback = arguments[1];
    }
    console_log("Adding Coach.  Send Email: " + values["sendemail"]);

    values["userType"]=USER_TYPE.COACH;
    api_call("PUT", SERVER_URI +'/coachs',values,callback);
}

function addSession(values, callback) {
    api_call("PUT", SERVER_URI +'/sessions',values,callback);
}

function updateTimezone(value, useDaylightSavings, callback) {
   var values = new Object;
    values["timezone"] =value;
    if (useDaylightSavings) values["daylight-savings"] = 1; else values["daylight-savings"] = 0;
    api_call("POST", SERVER_URI +'/users/',values,callback);
}

function updateClient() { // clientId, values, optional sendemail=false, sendnotification=false callback){
    if (arguments.length!=3 && arguments.length!=5) {
        throw "Invalid arguments.  Must send: clientId, values, callback or clientId, values, sendemail, sendnotification, callback.";

        return;
    }
    var clientId = arguments[0];
    var values = arguments[1];
    var callback;
    if (arguments.length==5) {
        values["sendemail"] = arguments[2];
        values["sendnotification"] = arguments[3];
        callback = arguments[4];
    } else {
        values["sendemail"] = false;
        values["sendnotification"] = false;
        callback = arguments[2];
    }

    api_call("POST", SERVER_URI +'/clients/' + clientId,values,callback);
}

function updateOrganization() { // organizationId, values, optional sendemail=false, callback){
    if (arguments.length!=3 && arguments.length!=4) {
        throw "Invalid arguments.  Must send: orgId, values, callback or orgId, values, sendemail, callback.";
        return;
    }
    var organizationId = arguments[0];
    var values = arguments[1];
    var callback;
    if (arguments.length==4) {
        values["sendemail"] = arguments[2];
        callback = arguments[3];
    } else {
        values["sendemail"] = false;
        callback = arguments[2];
    }

    api_call("POST", SERVER_URI +'/organizations/' + organizationId,values,callback);
}

function updateSession(sessionId, values, callback) {
    api_call("POST", SERVER_URI +'/sessions/' + sessionId,values,callback);
}
function updateCoach() { // coachId, values, optional sendemail=false, callback){
    if (arguments.length!=3 && arguments.length!=4) {
        throw "Invalid arguments.  Must send: coachId, values, callback or coachId, values, sendemail, callback.";
        return;
    }
    var coachId = arguments[0];
    var values = arguments[1];
    var callback;

    if (arguments.length==4) {
        values["sendemail"] = arguments[2];
        callback = arguments[3];
    } else {
        values["sendemail"] = false;
        callback = arguments[2];
    }
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
   if (tags=="") tags = "all";
   api_call("GET", SERVER_URI +'/report/' + startdate + "/"  + enddate  + "/" + organizationId + "/" + tags,null,callback);
}

function downloadAccountingReport(startdate, enddate, organizationId, tags, callback) {

    console_log("Downloading acct report");

    if (tags=="") tags = "all";
    var linkHref = SERVER_URI +'/report/' + startdate + "/" + enddate + "/" + organizationId + "/" + tags + "/csv";
    console_log(linkHref);
    $.fileDownload(linkHref);
console_log("OK");
    if (callback) callback(1,null);
}

function downloadProgressReport(clientId, startdate, enddate, callback) {
    window.open(SERVER_URI +'/progress_report/' + startdate + "/" + enddate + "/" + clientId);
    if (callback) callback(1,null);
}

function api_call(type, url, data, callback) {
    if (!isIE)  console_log("Sending  to " + url);
    //if (!isIE) console_log(data);
    if (data==null || data==undefined) data = new Object();
    data["session_id"] = session_id;
    console_log(data);
    $.ajax({
        type: type,
        url: url,
        data: data,
        cache: false,
        crossDomain: true,
        success: function(data) {
            if (data!=undefined && data.session_id!=undefined) session_id = data.session_id;
            if (!isIE)  console_log(data);
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
            if (!isIE) console_log("Error: " + textStatus);
            //if (!isIE) console_log(jqXHR);
            if (!isIE) console_log(errorThrown);
            callback(-1,textStatus + errorThrown);
        },
        dataType: "json"
    });
}

/* A JavaScript implementation of the SHA family of hashes, as defined in FIPS
 * PUB 180-2 as well as the corresponding HMAC implementation as defined in
 * FIPS PUB 198a
 *
 * Version 1.31 Copyright Brian Turek 2008-2012
 * Distributed under the BSD License
 * See http://caligatio.github.com/jsSHA/ for more information
 *
 * Several functions taken from Paul Johnson
 */
(function(){var charSize=8,b64pad="",hexCase=0,Int_64=function(a,b){this.highOrder=a;this.lowOrder=b},str2binb=function(a){var b=[],mask=(1<<charSize)-1,length=a.length*charSize,i;for(i=0;i<length;i+=charSize){b[i>>5]|=(a.charCodeAt(i/charSize)&mask)<<(32-charSize-(i%32))}return b},hex2binb=function(a){var b=[],length=a.length,i,num;for(i=0;i<length;i+=2){num=parseInt(a.substr(i,2),16);if(!isNaN(num)){b[i>>3]|=num<<(24-(4*(i%8)))}else{return"INVALID HEX STRING"}}return b},binb2hex=function(a){var b=(hexCase)?"0123456789ABCDEF":"0123456789abcdef",str="",length=a.length*4,i,srcByte;for(i=0;i<length;i+=1){srcByte=a[i>>2]>>((3-(i%4))*8);str+=b.charAt((srcByte>>4)&0xF)+b.charAt(srcByte&0xF)}return str},binb2b64=function(a){var b="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz"+"0123456789+/",str="",length=a.length*4,i,j,triplet;for(i=0;i<length;i+=3){triplet=(((a[i>>2]>>8*(3-i%4))&0xFF)<<16)|(((a[i+1>>2]>>8*(3-(i+1)%4))&0xFF)<<8)|((a[i+2>>2]>>8*(3-(i+2)%4))&0xFF);for(j=0;j<4;j+=1){if(i*8+j*6<=a.length*32){str+=b.charAt((triplet>>6*(3-j))&0x3F)}else{str+=b64pad}}}return str},rotr=function(x,n){if(n<=32){return new Int_64((x.highOrder>>>n)|(x.lowOrder<<(32-n)),(x.lowOrder>>>n)|(x.highOrder<<(32-n)))}else{return new Int_64((x.lowOrder>>>n)|(x.highOrder<<(32-n)),(x.highOrder>>>n)|(x.lowOrder<<(32-n)))}},shr=function(x,n){if(n<=32){return new Int_64(x.highOrder>>>n,x.lowOrder>>>n|(x.highOrder<<(32-n)))}else{return new Int_64(0,x.highOrder<<(32-n))}},ch=function(x,y,z){return new Int_64((x.highOrder&y.highOrder)^(~x.highOrder&z.highOrder),(x.lowOrder&y.lowOrder)^(~x.lowOrder&z.lowOrder))},maj=function(x,y,z){return new Int_64((x.highOrder&y.highOrder)^(x.highOrder&z.highOrder)^(y.highOrder&z.highOrder),(x.lowOrder&y.lowOrder)^(x.lowOrder&z.lowOrder)^(y.lowOrder&z.lowOrder))},sigma0=function(x){var a=rotr(x,28),rotr34=rotr(x,34),rotr39=rotr(x,39);return new Int_64(a.highOrder^rotr34.highOrder^rotr39.highOrder,a.lowOrder^rotr34.lowOrder^rotr39.lowOrder)},sigma1=function(x){var a=rotr(x,14),rotr18=rotr(x,18),rotr41=rotr(x,41);return new Int_64(a.highOrder^rotr18.highOrder^rotr41.highOrder,a.lowOrder^rotr18.lowOrder^rotr41.lowOrder)},gamma0=function(x){var a=rotr(x,1),rotr8=rotr(x,8),shr7=shr(x,7);return new Int_64(a.highOrder^rotr8.highOrder^shr7.highOrder,a.lowOrder^rotr8.lowOrder^shr7.lowOrder)},gamma1=function(x){var a=rotr(x,19),rotr61=rotr(x,61),shr6=shr(x,6);return new Int_64(a.highOrder^rotr61.highOrder^shr6.highOrder,a.lowOrder^rotr61.lowOrder^shr6.lowOrder)},safeAdd_2=function(x,y){var a,msw,lowOrder,highOrder;a=(x.lowOrder&0xFFFF)+(y.lowOrder&0xFFFF);msw=(x.lowOrder>>>16)+(y.lowOrder>>>16)+(a>>>16);lowOrder=((msw&0xFFFF)<<16)|(a&0xFFFF);a=(x.highOrder&0xFFFF)+(y.highOrder&0xFFFF)+(msw>>>16);msw=(x.highOrder>>>16)+(y.highOrder>>>16)+(a>>>16);highOrder=((msw&0xFFFF)<<16)|(a&0xFFFF);return new Int_64(highOrder,lowOrder)},safeAdd_4=function(a,b,c,d){var e,msw,lowOrder,highOrder;e=(a.lowOrder&0xFFFF)+(b.lowOrder&0xFFFF)+(c.lowOrder&0xFFFF)+(d.lowOrder&0xFFFF);msw=(a.lowOrder>>>16)+(b.lowOrder>>>16)+(c.lowOrder>>>16)+(d.lowOrder>>>16)+(e>>>16);lowOrder=((msw&0xFFFF)<<16)|(e&0xFFFF);e=(a.highOrder&0xFFFF)+(b.highOrder&0xFFFF)+(c.highOrder&0xFFFF)+(d.highOrder&0xFFFF)+(msw>>>16);msw=(a.highOrder>>>16)+(b.highOrder>>>16)+(c.highOrder>>>16)+(d.highOrder>>>16)+(e>>>16);highOrder=((msw&0xFFFF)<<16)|(e&0xFFFF);return new Int_64(highOrder,lowOrder)},safeAdd_5=function(a,b,c,d,e){var f,msw,lowOrder,highOrder;f=(a.lowOrder&0xFFFF)+(b.lowOrder&0xFFFF)+(c.lowOrder&0xFFFF)+(d.lowOrder&0xFFFF)+(e.lowOrder&0xFFFF);msw=(a.lowOrder>>>16)+(b.lowOrder>>>16)+(c.lowOrder>>>16)+(d.lowOrder>>>16)+(e.lowOrder>>>16)+(f>>>16);lowOrder=((msw&0xFFFF)<<16)|(f&0xFFFF);f=(a.highOrder&0xFFFF)+(b.highOrder&0xFFFF)+(c.highOrder&0xFFFF)+(d.highOrder&0xFFFF)+(e.highOrder&0xFFFF)+(msw>>>16);msw=(a.highOrder>>>16)+(b.highOrder>>>16)+(c.highOrder>>>16)+(d.highOrder>>>16)+(e.highOrder>>>16)+(f>>>16);highOrder=((msw&0xFFFF)<<16)|(f&0xFFFF);return new Int_64(highOrder,lowOrder)},coreSHA2=function(j,k,l){var a,b,c,d,e,f,g,h,T1,T2,H,lengthPosition,i,t,K,W=[],appendedMessageLength;if(l==="SHA-384"||l==="SHA-512"){lengthPosition=(((k+128)>>10)<<5)+31;K=[new Int_64(0x428a2f98,0xd728ae22),new Int_64(0x71374491,0x23ef65cd),new Int_64(0xb5c0fbcf,0xec4d3b2f),new Int_64(0xe9b5dba5,0x8189dbbc),new Int_64(0x3956c25b,0xf348b538),new Int_64(0x59f111f1,0xb605d019),new Int_64(0x923f82a4,0xaf194f9b),new Int_64(0xab1c5ed5,0xda6d8118),new Int_64(0xd807aa98,0xa3030242),new Int_64(0x12835b01,0x45706fbe),new Int_64(0x243185be,0x4ee4b28c),new Int_64(0x550c7dc3,0xd5ffb4e2),new Int_64(0x72be5d74,0xf27b896f),new Int_64(0x80deb1fe,0x3b1696b1),new Int_64(0x9bdc06a7,0x25c71235),new Int_64(0xc19bf174,0xcf692694),new Int_64(0xe49b69c1,0x9ef14ad2),new Int_64(0xefbe4786,0x384f25e3),new Int_64(0x0fc19dc6,0x8b8cd5b5),new Int_64(0x240ca1cc,0x77ac9c65),new Int_64(0x2de92c6f,0x592b0275),new Int_64(0x4a7484aa,0x6ea6e483),new Int_64(0x5cb0a9dc,0xbd41fbd4),new Int_64(0x76f988da,0x831153b5),new Int_64(0x983e5152,0xee66dfab),new Int_64(0xa831c66d,0x2db43210),new Int_64(0xb00327c8,0x98fb213f),new Int_64(0xbf597fc7,0xbeef0ee4),new Int_64(0xc6e00bf3,0x3da88fc2),new Int_64(0xd5a79147,0x930aa725),new Int_64(0x06ca6351,0xe003826f),new Int_64(0x14292967,0x0a0e6e70),new Int_64(0x27b70a85,0x46d22ffc),new Int_64(0x2e1b2138,0x5c26c926),new Int_64(0x4d2c6dfc,0x5ac42aed),new Int_64(0x53380d13,0x9d95b3df),new Int_64(0x650a7354,0x8baf63de),new Int_64(0x766a0abb,0x3c77b2a8),new Int_64(0x81c2c92e,0x47edaee6),new Int_64(0x92722c85,0x1482353b),new Int_64(0xa2bfe8a1,0x4cf10364),new Int_64(0xa81a664b,0xbc423001),new Int_64(0xc24b8b70,0xd0f89791),new Int_64(0xc76c51a3,0x0654be30),new Int_64(0xd192e819,0xd6ef5218),new Int_64(0xd6990624,0x5565a910),new Int_64(0xf40e3585,0x5771202a),new Int_64(0x106aa070,0x32bbd1b8),new Int_64(0x19a4c116,0xb8d2d0c8),new Int_64(0x1e376c08,0x5141ab53),new Int_64(0x2748774c,0xdf8eeb99),new Int_64(0x34b0bcb5,0xe19b48a8),new Int_64(0x391c0cb3,0xc5c95a63),new Int_64(0x4ed8aa4a,0xe3418acb),new Int_64(0x5b9cca4f,0x7763e373),new Int_64(0x682e6ff3,0xd6b2b8a3),new Int_64(0x748f82ee,0x5defb2fc),new Int_64(0x78a5636f,0x43172f60),new Int_64(0x84c87814,0xa1f0ab72),new Int_64(0x8cc70208,0x1a6439ec),new Int_64(0x90befffa,0x23631e28),new Int_64(0xa4506ceb,0xde82bde9),new Int_64(0xbef9a3f7,0xb2c67915),new Int_64(0xc67178f2,0xe372532b),new Int_64(0xca273ece,0xea26619c),new Int_64(0xd186b8c7,0x21c0c207),new Int_64(0xeada7dd6,0xcde0eb1e),new Int_64(0xf57d4f7f,0xee6ed178),new Int_64(0x06f067aa,0x72176fba),new Int_64(0x0a637dc5,0xa2c898a6),new Int_64(0x113f9804,0xbef90dae),new Int_64(0x1b710b35,0x131c471b),new Int_64(0x28db77f5,0x23047d84),new Int_64(0x32caab7b,0x40c72493),new Int_64(0x3c9ebe0a,0x15c9bebc),new Int_64(0x431d67c4,0x9c100d4c),new Int_64(0x4cc5d4be,0xcb3e42b6),new Int_64(0x597f299c,0xfc657e2a),new Int_64(0x5fcb6fab,0x3ad6faec),new Int_64(0x6c44198c,0x4a475817)];if(l==="SHA-384"){H=[new Int_64(0xcbbb9d5d,0xc1059ed8),new Int_64(0x0629a292a,0x367cd507),new Int_64(0x9159015a,0x3070dd17),new Int_64(0x0152fecd8,0xf70e5939),new Int_64(0x67332667,0xffc00b31),new Int_64(0x98eb44a87,0x68581511),new Int_64(0xdb0c2e0d,0x64f98fa7),new Int_64(0x047b5481d,0xbefa4fa4)]}else{H=[new Int_64(0x6a09e667,0xf3bcc908),new Int_64(0xbb67ae85,0x84caa73b),new Int_64(0x3c6ef372,0xfe94f82b),new Int_64(0xa54ff53a,0x5f1d36f1),new Int_64(0x510e527f,0xade682d1),new Int_64(0x9b05688c,0x2b3e6c1f),new Int_64(0x1f83d9ab,0xfb41bd6b),new Int_64(0x5be0cd19,0x137e2179)]}}j[k>>5]|=0x80<<(24-k%32);j[lengthPosition]=k;appendedMessageLength=j.length;for(i=0;i<appendedMessageLength;i+=32){a=H[0];b=H[1];c=H[2];d=H[3];e=H[4];f=H[5];g=H[6];h=H[7];for(t=0;t<80;t+=1){if(t<16){W[t]=new Int_64(j[t*2+i],j[t*2+i+1])}else{W[t]=safeAdd_4(gamma1(W[t-2]),W[t-7],gamma0(W[t-15]),W[t-16])}T1=safeAdd_5(h,sigma1(e),ch(e,f,g),K[t],W[t]);T2=safeAdd_2(sigma0(a),maj(a,b,c));h=g;g=f;f=e;e=safeAdd_2(d,T1);d=c;c=b;b=a;a=safeAdd_2(T1,T2)}H[0]=safeAdd_2(a,H[0]);H[1]=safeAdd_2(b,H[1]);H[2]=safeAdd_2(c,H[2]);H[3]=safeAdd_2(d,H[3]);H[4]=safeAdd_2(e,H[4]);H[5]=safeAdd_2(f,H[5]);H[6]=safeAdd_2(g,H[6]);H[7]=safeAdd_2(h,H[7])}switch(l){case"SHA-384":return[H[0].highOrder,H[0].lowOrder,H[1].highOrder,H[1].lowOrder,H[2].highOrder,H[2].lowOrder,H[3].highOrder,H[3].lowOrder,H[4].highOrder,H[4].lowOrder,H[5].highOrder,H[5].lowOrder];case"SHA-512":return[H[0].highOrder,H[0].lowOrder,H[1].highOrder,H[1].lowOrder,H[2].highOrder,H[2].lowOrder,H[3].highOrder,H[3].lowOrder,H[4].highOrder,H[4].lowOrder,H[5].highOrder,H[5].lowOrder,H[6].highOrder,H[6].lowOrder,H[7].highOrder,H[7].lowOrder];default:return[]}},jsSHA=function(a,b){this.sha384=null;this.sha512=null;this.strBinLen=null;this.strToHash=null;if("HEX"===b){if(0!==(a.length%2)){return"TEXT MUST BE IN BYTE INCREMENTS"}this.strBinLen=a.length*4;this.strToHash=hex2binb(a)}else if(("ASCII"===b)||('undefined'===typeof(b))){this.strBinLen=a.length*charSize;this.strToHash=str2binb(a)}else{return"UNKNOWN TEXT INPUT TYPE"}};jsSHA.prototype={getHash:function(a,b){var c=null,message=this.strToHash.slice();switch(b){case"HEX":c=binb2hex;break;case"B64":c=binb2b64;break;default:return"FORMAT NOT RECOGNIZED"}switch(a){case"SHA-384":if(null===this.sha384){this.sha384=coreSHA2(message,this.strBinLen,a)}return c(this.sha384);case"SHA-512":if(null===this.sha512){this.sha512=coreSHA2(message,this.strBinLen,a)}return c(this.sha512);default:return"HASH NOT RECOGNIZED"}},getHMAC:function(a,b,c,d){var e,keyToUse,i,retVal,keyBinLen,hashBitSize,keyWithIPad=[],keyWithOPad=[];switch(d){case"HEX":e=binb2hex;break;case"B64":e=binb2b64;break;default:return"FORMAT NOT RECOGNIZED"}switch(c){case"SHA-384":hashBitSize=384;break;case"SHA-512":hashBitSize=512;break;default:return"HASH NOT RECOGNIZED"}if("HEX"===b){if(0!==(a.length%2)){return"KEY MUST BE IN BYTE INCREMENTS"}keyToUse=hex2binb(a);keyBinLen=a.length*4}else if("ASCII"===b){keyToUse=str2binb(a);keyBinLen=a.length*charSize}else{return"UNKNOWN KEY INPUT TYPE"}if(128<(keyBinLen/8)){keyToUse=coreSHA2(keyToUse,keyBinLen,c);keyToUse[31]&=0xFFFFFF00}else if(128>(keyBinLen/8)){keyToUse[31]&=0xFFFFFF00}for(i=0;i<=31;i+=1){keyWithIPad[i]=keyToUse[i]^0x36363636;keyWithOPad[i]=keyToUse[i]^0x5C5C5C5C}retVal=coreSHA2(keyWithIPad.concat(this.strToHash),1024+this.strBinLen,c);retVal=coreSHA2(keyWithOPad.concat(retVal),1024+hashBitSize,c);return(e(retVal))}};window.jsSHA=jsSHA}());

function hex_sha512(str) {
    hashObj = new jsSHA(str, 'ASCII');
    return hashObj.getHash('SHA-512', "HEX");
}

function console_log(msg) {
    if (!isIE && debug) console.log(msg);
}


var recent_error = false;
function reportError() {

    if ((arguments.length!=4 && arguments.length!=5) || (arguments.length==4 && !((typeof arguments[1] == "object") && (arguments[1] !== null) ))) {
        throw "Invalid arguments.  Must send: alertmsg, jqxhr, exception, settings or alertmsg, function, location, code, exception";
        return;
    }

    var errorListStr = cookie.get('errors');
    var errorList = [];
    try {
        if (errorListStr && errorListStr != "") {
            errorList = JSON.parse(errorListStr);
        }
    } catch (e) {
        errorList = [];
    }

    var err = new Object();
    err.user = currentUser;
    err.sessionId = session_id;
    err.browserUserAgent = navigator.userAgent;
    err.browserAppVersion = navigator.appVersion;

    if (arguments.length==4) {
        var jqxhr = arguments[1];
        var exception = arguments[2];
        var settings = arguments[3];

        err.jqxhrResponseText = jqxhr.responseText;
        err.jqxhrStatus = jqxhr.status;
        err.settingsType = settings.type;
        err.settingsUrl = settings.url;
        err.exception = exception;
        errorList.push(err);

    } else {
        err.context = arguments[1];
        err.fnction  = arguments[2];
        err.code = arguments[3];
        err.exception = arguments[4];
    }

    if (arguments[0]!="" && !recent_error) {
        custom_alert(arguments[0]);
        recent_error = true;
        setTimeout(function(){recent_error=false},10000);
    }

    try {
        errorList.push(err);
        cookie.set('errors', JSON.stringify(errorList));
        sendErrors(JSON.stringify(errorList), function(success, data) {
            if (success==1) cookie.set('errors', '');
        });
        return true;
    } catch (e) {
        return false;
    }
}

function custom_alert(msg) {
    alert(msg);
    //todo - make this more in line with site style...  CSS Modal dialog rather than system dialog.
}