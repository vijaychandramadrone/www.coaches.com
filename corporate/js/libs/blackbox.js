var rootURL = "http://project-files.net/cti/api";

function getList(table, callback) {
    console.log("Calling table: " + rootURL + '/' + table);
    $.ajax({
        type: 'GET',
        url: rootURL + '/' + table,
        dataType: "json", // data type of response
        success: function(data, textStatus, jqXHR){
            console.log('Get successfully');
            callback(data);
        },
        error: function(jqXHR, textStatus, errorThrown){
            console.log('get error: ' + textStatus);
            callback(false);
        }
    });
}

function getRowById(table, id, callback) {
    $.ajax({
        type: 'GET',
        url: rootURL + '/' + table + '/' + id,
        dataType: "json",
        success: function(data, textStatus, jqXHR){
            console.log('Get successfully');
            callback(data);
        },
        error: function(jqXHR, textStatus, errorThrown){
            console.log('get error: ' + textStatus);
            callback(false);
        }
    });
}

function getRowByField(table, field, value, callback) {
    $.ajax({
        type: 'GET',
        url: rootURL + '/' + table + '/search/' + field + '/' + value,
        dataType: "json",
        success: function(data, textStatus, jqXHR){
            console.log('Get successfully');
            callback(data);
        },
        error: function(jqXHR, textStatus, errorThrown){
            console.log('get error: ' + textStatus);
            callback(false);
        }
    });
}



function addRow(table, rowData, callback) {
    $.ajax({
        type: 'POST',
        contentType: 'application/json',
        url: rootURL + '/' + table,
        dataType: "json",
        data: rowData,
        success: function(data, textStatus, jqXHR){
            console.log('Created successfully');
            callback(data);
        },
        error: function(jqXHR, textStatus, errorThrown){
            console.log('create error: ' + textStatus);
            callback(false);
        }
    });
}

function updateRow(table, id, rowData, callback) {
    $.ajax({
        type: 'PUT',
        contentType: 'application/json',
        url: rootURL + '/' + table + '/' + id,
        dataType: "json",
        data: rowData,
        success: function(data, textStatus, jqXHR){
            console.log('Updated successfully');
            callback(data);
        },
        error: function(jqXHR, textStatus, errorThrown){
            console.log('update error: ' + textStatus);
            callback(false);
        }
    });
}

function deleteRow(table, id, callback) {
    $.ajax({
        type: 'DELETE',
        url: rootURL + '/' + table + '/' + id,
        success: function(data, textStatus, jqXHR){
            console.log("Delete successful");
            callback(true);
        },
        error: function(jqXHR, textStatus, errorThrown){
            console.log("Delete failed.");
            callback(false);
        }
    });
}
