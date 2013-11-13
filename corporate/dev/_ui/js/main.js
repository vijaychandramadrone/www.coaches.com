/*******************************************************************************

 CSS on Sails Framework
 Title: CTI
 Author: XHTMLized (http://www.xhtmlized.com/)
 Date: June 2012

 *******************************************************************************/

var Site = {
    init: function() {
        $('body').removeClass('no-js');

        $('.sidebar .wrapper > div').not(':eq(0)').hide();
        $('.content .wrapper > div').not(':eq(0)').hide();

        $(document).ajaxStart(function(){
            $('body').append($('<div class="data-loading"></div>'));
        });

        $(document).ajaxStop(function(){
            $('.data-loading').remove();
            if ($('.container').is(':hidden')) {
                $('.container').show();
            };
        });
    },

    tabs: function(elem) {
        $(elem).live('click', function(e){
            var id = $(this).find('a').attr('href');
            $(this).addClass('active').siblings().removeClass('active');
            $(id).show().siblings().hide();

            e.preventDefault();
        });
    },

    tags: function() {
        $('.tags').find('a').on('click', function(e){
            $(this).siblings().fadeToggle(200);

            e.stopPropagation();
            e.preventDefault();
        });

        $('.tags').find('> div').on('click', function(e){
            e.stopPropagation();
        });

        $(document).on('click', function(){
            $('.tags').find('> div').hide();
        });
    },

    startDate: function(){
        return '01-01-01'
    },

    currentDate: function(){
        var date = new Date(),
            month = (date.getMonth() < 9) ? ('0' + (date.getMonth() + 1)) : (date.getMonth() + 1),
            day = (date.getDate() < 10) ? ('0' + date.getDate()) : (date.getDate()),
            year = date.getFullYear().toString(10).substring(2,4);

        return month + "-" + day + "-" + year;
    },

    time: function(date){
        var parts = date.split(':'),
            hour = parseInt(parts[0], 10),
            minutes = parts[1],
            newDate;

        if (hour > 12) {
            newDate = (hour - 12) + ':' + minutes + ' pm';
        } else if (hour === 00) {
            newDate = 12 + ':' + minutes + ' am';
        } else if (hour === 12) {
            newDate = hour + ':' + minutes + ' pm';
        } else if (hour === (10 || 11 || 12)) {
            newDate = hour + ':' + minutes + ' am';
        } else {
            newDate = hour + ':' + minutes + ' am';
        }
        
        return newDate;
    },

    newTabPrevent: function(){
        $(document).on('click', function(e) {
            if (e.which === 2) {
                e.preventDefault();
            }
        });

        $(document).on('contextmenu', function(e) {
            e.preventDefault();
        });
    },

    encodeString: function(str){
        return str.replace(/\\n/g, '\n').replace(/\\'/g, '\'').replace(/\\"/g, '\"').replace(/\\\\/g, '\\');
    },

    valuesSwap: function(from, to, callback){
        var props = [];

        for (prop in from) {
            to[prop] = from[prop];
            props.push(prop)
        }

        if (callback) {
            callback(props);
        }
    },

    removeItem: function(obj){
        obj.splice(Data.item.index(), 1);
        Data.item.remove();
        Overlay.closeTrigger();        
    }
};

var Overlay = {
    open: function(callback) {
        var overlay = '<div class="overlay-bg"></div><div class="overlay-wrapper"><div class="overlay"></div></div>',
            url = $(this).is('a') ? $(this).attr('href') : $(this).find('a').attr('href');

        $('body').append(overlay).css('overflow', 'hidden');
        $('.overlay').load(url, function(){
            if (callback !== undefined) {
                callback();
            } else {
                return;
            }
        });
        $('.overlay-wrapper').hide().fadeIn(100);
    },

    openLightbox: function(){
        $(document).on('click', '.lightbox', function(e){
            Overlay.open.call(this);
            e.preventDefault();
        });
    },

    close: function() {
        $(document).on('click', '.overlay-wrapper', function(){
            Overlay.closeTrigger();
        });

        $(document).on('click', '.overlay', function(e){
            if ($('.dk_open').length) {
                $('.dk_open').removeClass('dk_open');
                $('.dk_options').hide();
            };

            e.stopPropagation();
        });

        $(document).on('click', '.overlay-close', function(){
            Overlay.closeTrigger();
        });

        $(document).on('click', 'input[value="Cancel"]', function(){
            Overlay.closeTrigger();
        });
    },

    closeTrigger: function(){
        $('.overlay-wrapper, .overlay-bg').fadeOut(100, function(){
            $(this).remove();
        });
        $('body').css('overflow', 'auto');
    }
};

var Table = {
    fixedHeader: function(elem) {
        var h = $(elem).outerHeight(true);
        $(elem).find('table').fixheadertable({
            height: h,
            sortable: true,
            wrapper: false,
            resizeCol: true,
            zebra: true
        });
    },

    fixedHeaderHover: function(elem) {
        $(elem).find('th').on('click', function(){
            $(elem).each(function(){
                if($(this).find('th:last-child').hasClass('ui-state-hover')) {
                    $(this).find('.headtable > div').addClass('ui-head-active');
                } else {
                    $(this).find('.headtable > div').removeClass('ui-head-active');
                }
            });
        });
    }
}

var Plugins = {
    init: function(){
        if (!isIE) $('input[placeholder], textarea[placeholder]').placeholder();
        $('a.tooltip').live('mouseover', function(){
            $(this).qtip({
                overwrite: false,
                show: {
                    ready: true
                },
                position: {
                    my: 'top center',
                    at: 'bottom center'
                }
            });
        });

        $('.datepicker').datepicker({
            dateFormat: 'mm-dd-y'
        })

        $('.notes-list').find('.edit').live('click', function(e){
            $(this).parent().siblings('.note-content').find('.editable').editable(function(value, settings) {
                return(value);
            }, {
                cssclass: 'editable-content',
                type: 'textarea',
                cancel: 'Cancel',
                submit: 'Save Changes',
                tooltip: 'Click to edit...',
                onblur: 'ignore',
                ownCallback: function(){
                    if (('.notes-list').length !== 0) {
                        $('.notes-list .active').parents('li').find('.note-actions').show();
                        $('.notes-list .active').parents('li').find('.edit').removeClass('active');
                    }
                }
            }).click();

            $(this).addClass('active');
            $(this).parents('li').find('.note-actions').hide();
            e.preventDefault();
        });


        $(document).on('click', function(){
            if ($('.dk_open').length) {
                $('.dk_open').removeClass('dk_open');
                $('.dk_options').hide();
            };
        });
    },

    initOverlay: function(){
        $('input[placeholder], textarea[placeholder]').placeholder();

        $('.datepicker').datepicker({
            dateFormat: 'mm-dd-y'
        });

        if ($.timepicker !== undefined ) {
            $('#new-session-time-from').timepicker({
                showLeadingZero: false,
                showPeriod: true,
                amPmText: ['am', 'pm'],
                defaultTime: '12:00'
            });

            $('#new-session-time-to').timepicker({
                showHours: false,
                rows: 4,
                minutes: {
                    starts: 0,
                    ends: 90,
                    interval: 5
                }
            });
        }

        $('.overlay select').dropkick();

        Table.fixedHeader('.overlay .table-wrapper');
        Table.fixedHeaderHover('.overlay .table-wrapper');

        $('.editable').live('click', function(){
            $(this).editable(function(value, settings) {
                return(value);
            }, {
                cssclass: 'editable-content',
                type: 'textarea',
                cancel: 'Cancel',
                submit: 'Save Changes',
                tooltip: 'Click to edit...'
            });
        });
    }
}

var Login = {
    validation: function() {
        if (typeof cookie == 'undefined') return true;

        if (cookie.get('ctiLoginRemember') === 'perm') {
            var un = cookie.get('ctiLoginStorage');
            pw = cookie.get('ctiPassStorage');

            login(un, pw, function(success,data) {
                console_log(success);
                console_log(data);
                Login.redirect(success,data);
            });
        };

        $('#login').find('input#login-submit').on('click', function(e) {
            var un = $('#login').find('input#login-email').val(),
                pw = hex_sha512($('#login').find('input#login-password').val());
            if ($('#login-remember').is(':checked')) {
                cookie.set('ctiLoginRemember', 'perm', {
                    expires: 365
                });
            };

            login(un, pw, function(success,data) {
                if (success==1) {
                    cookie.set('ctiLoginStorage', un, {
                        expires: 365
                    });
                    cookie.set('ctiPassStorage', pw, {
                        expires: 365
                    });
                }

                console_log(success);
                //console_log(data);
                Login.redirect(success,data);
            });

            e.preventDefault();
        });

        $('#login').find('input#register-submit').on('click', function(e) {
            e.preventDefault();

            $(".error").hide();

            var regexS = "[\\?&]r=([^&#]*)";
            var regex = new RegExp( regexS );
            var results = regex.exec( window.location.href );
            var registration_code = "";
            if( results != null ) registration_code = results[1];

            var un = $('#login').find('input#login-email').val(),
                pw = hex_sha512($('#login').find('input#login-password').val()),
                pwconf = hex_sha512($('#login').find('input#login-password-confirmation').val());

            if (pw!=pwconf) {
                $(".error").show();
                return;
            }

            updateLogin(registration_code, un, pw, function(success,data) {
                if (success!=1) {
                    $(".error").text(data.message);
                    $(".error").show();
                    return;
                }

                cookie.set('ctiLoginStorage', un, {
                    expires: 365
                });
                cookie.set('ctiPassStorage', pw, {
                    expires: 365
                });

                console_log(success);
                //console_log(data);
                Login.redirect(success,data);
            });
        });

        $(document).on('keydown', function(e){
            if (e.which === 13) {
                $('input#login-submit').click();
            }
        });

        Plugins.init();
    },

    redirect: function(success,data){
        // console_log("Doing redirect" + success + data.code);
        if (success==1) {
            switch (data.code) {
                case '1':
                    window.location = "account-manager.html";
                    break;
                case '2':
                    window.location = "organization.html";
                    break;
                case '3':
                    window.location = "coach.html";
                    break;
                case '4':
                    window.location = "client.html";
                    break;
                case '5':
                    window.location = "accounting.html";
                    break;
            }
        } else if (success==0) {
            $('#login').find('.error').show();
        } else {
            alert("Login failed.  Could not connect to server. " + data);
        }
    },

    forgot: function() {
        $('#login-retrieve-submit').on('click', function(e) {
            var email = $('#login-retrieve-email').val();

            console_log('')
            console_log('Sent data values:');
            console_log(email);
            console_log('')

            forgot(email, function(success, data) {
                //console_log(data);

                if($('#login').find('p.message').length === 0) {
                    $('<p class="message"></p>').text(data.message).insertAfter('p.retrieve');
                } else {
                    $('p.message').text(data.message);
                }
            })

            e.preventDefault();
        });
    },

    logout: function() {
        $('a[title="Logout"]').on('click', function(){
            logout(function(success, data){
                cookie.remove('ctiLoginStorage');
                cookie.remove('ctiPassStorage');
                cookie.remove('ctiLoginRemember');

                window.location = "index.html";
            });
        })
    }
};

var Data = {
    init: function() {
        var self = this,
            un = cookie.get('ctiLoginStorage'),
            pw = cookie.get('ctiPassStorage');

        if (cookie.get('ctiPassStorage') === undefined || cookie.get('ctiLoginStorage') === undefined)  {
            window.location = "index.html";
        } else {
            login(un, pw, function(success,data) {
                Data.timezone = data.timezone;
                Data.daylight = data['daylight-savings'];

                $('.welcome').text('Welcome, ' + data.message);
                //data.timezone && $('.user-timezone').text('Settings'); //$('.user-timezone').text('UTC' + (data.timezone >= 0 ? '+' : '') + data.timezone);

                $('.user-timezone').text('Settings');

                switch (data.code) {
                    case '1':
                        if (window.location.pathname.indexOf('accounting') !== -1) {
                            self.initAccounting();
                        } else {
                            self.initAdmin();
                        }
                        break;
                    case '2':
                        self.initOrganization();
                        break;
                    case '3':
                        self.initCoach();
                        break;
                    case '4':
                        self.initClient();
                        break;
                    case '5':
                        self.initAccounting();
                        break;
                };
            });
        };
    },

    initAdmin: function(){
        var self = this;

        if ($('body').hasClass('orgAdmin')) {
            self.initAdminOrg();
        } else {
            if (Data.timezone === undefined) {
                self.overlays.setTimezone();
            };

            getOrganizations(function(success, data) {
                self.organizationsData = data;
                self.templates.adminOrganizations(data);

                self.overlays.deleteOrganization('#organizations-side .delete');
                self.overlays.editOrganization('#organizations-side .edit');
                self.overlays.newOrganization('.new-organization');
                self.overlays.viewOrganization('#organizations-side .title, #organizations-side .view');
            });

            getCoaches(function(success, data) {
                self.coachesData = data;
                self.templates.adminCoaches(data);

                self.overlays.coachesSide('#coaches-side .title, #coaches-side .view');
                self.overlays.coachesOverlay();
                self.overlays.deleteCoach('#coaches-side .delete');
                self.overlays.editCoach('#coaches-side .edit', false);
                self.overlays.newCoach('.new-coach');
            });

            getDocumentTemplates(function(success, data) {
                self.documentsData = data;

                self.templates.adminDocuments(data);
                self.overlays.newDocument('.new-document');
                self.overlays.deleteDocument('.documents .delete');
                self.overlays.documentPrivileges('.privileges');
                self.overlays.linkDocuments('#new-document-link');
            });

            self.overlays.confirmation();
            Overlay.close();
        };
    },

    initAdminOrg: function(){
        var self = this,
            orgId = window.location.search.substring(4);

        getOrganizations(function(success,data){
            self.organizations = data;
            self.organizationsData = data;
            self.templates.accountingOrganizations(data);
            optionOrg = 'option[value="' + orgId + '"]';

            $('#companies-list').find(optionOrg).attr("selected", true);
            $('.bc-company').text($('#companies-list option:selected').text());
            $('select').dropkick();

            var logoIndex = $('#companies-list option:selected').index();

            if (data.organizations[logoIndex].logo !="") $('.logo').attr('src', data.organizations[logoIndex].logo);else $('.logo').hide();
            $('.company-name').text(data.organizations[logoIndex].organization_name);

            if($.browser.msie) {
                var tmpSelect,
                    setTimer;

                function selectTimer(){
                    if (tmpSelect !== $('#companies-list').val()) {
                        clearInterval(setTimer);
                        window.location = "account-manager-organization.html?id="+$('#companies-list').val();
                    }
                }

                $(document).on('click', '.tabs .dk_toggle', function () {
                    tmpSelect = $('#companies-list').val();
                    setTimer = setInterval(selectTimer, 200);
                });
            } else {
                $(document).on('click', '.container .dk_options a', function(){
                    window.location = "account-manager-organization.html?id="+$(this).attr('data-dk-dropdown-value');
                });
            }
        });

        getSessionsForOrganization(orgId, function(success, data) {
            Data.sessionsData = data;
            self.templates.organizationSession(data);

            $('tbody tr').each(function(){
                var i = $(this).index();
                $(this).find('.client, .coach').attr('data-index', i);
            });

            $('#all-count').find('span').text($('#all').find('tbody tr').length);
            $('#done-count').find('span').text($('#done').find('tbody tr').length);
            $('#noshows-count').find('span').text($('#noshows').find('tbody tr').length);
            $('#canceled-count').find('span').text($('#canceled').find('tbody tr').length);

            self.overlays.editOrganizationSession('.edit-session');
            self.overlays.deleteOrganizationSession('.delete-session');

            Table.fixedHeader('.content .table-wrapper');
            Table.fixedHeaderHover('.content .table-wrapper');
        });

        getDocumentTemplatesForOrganization(orgId, function(success, data) {
            self.templates.organizationDocuments(data);
        });

        getStatsForOrganization(orgId, function(success, data) {
            self.statsData = data;
            self.templates.organizationStats(data);
        });

        getClientsForOrganization(orgId, function(success, data) {
            self.clientsData = data;
            self.templates.organizationClients(data);

            self.overlays.clientsCount('#clients-count');
            self.overlays.clientsSide();
            self.overlays.clientsOverlay();
            self.overlays.newClient('.new-client');
            self.overlays.editClient('#clients-side .edit');
            self.overlays.deleteClient('#clients-side .delete');
            self.overlays.newSession('.new-session');

            $('.overlay').find('.edit').live('click', function(e){
                $(this).parent().next('.editable').editable(function(value, settings) {
                    return(value);
                }, {
                    cssclass: 'editable-content',
                    type: 'textarea',
                    cancel: 'Cancel',
                    submit: 'Save Changes',
                    tooltip: 'Click to edit...',
                    onblur: 'ignore',
                    ownCallback: function(){}
                }).click();

                $(this).addClass('active');

                e.preventDefault();
            });

            $(document).on('click', '.editable button[type="submit"]', function(){
                var clientId = $(this).parents('.form-content').find('li.active a').attr('data-id'),
                    values = {};

                console_log(clientId);

                if ($(this).parents('.focus-area').length) {
                    values.focus_area = $(this).siblings('textarea').val();
                } else if ($(this).parents('.success-metrics').length) {
                    values.success_metrics = $(this).siblings('textarea').val();
                };

                console_log('');
                console_log('Session ID:');
                console_log(clientId);
                console_log('Sent data values:');
                console_log(values);
                console_log('');

                updateClient(clientId, values, function(success, data){
                    console_log(success);
                    //console_log(data);

                    getClientsForOrganization(orgId, function(success, data) {
                        Data.clientsData = data;
                    });
                });
            });

            self.overlays.clientsReport('.download-report');
        });

        getCoachesForOrganization(orgId, function(success, data) {
            self.coachesData = data;
            self.templates.organizationCoaches(data);

            self.overlays.coachesCount('#coaches-count');
            self.overlays.coachesSide('#coaches-side .title');
            self.overlays.deleteCoach('#coaches-side .delete');
            self.overlays.editCoach('#coaches-side .edit', true);
            self.overlays.newCoach('.new-coach');
            self.overlays.coachesOverlay();
        });

        self.overlays.confirmation();
        Overlay.close();
    },

    initAccounting: function(){
        var self = this;

        if (Data.timezone === undefined) {
            self.overlays.setTimezone();
        };

        getOrganizations(function(success, data) {
            //console_log('organization initialization');

            self.templates.accountingOrganizations(data);
            $('#companies-list').dropkick();

            var dateFrom = Site.startDate(),
                dateTo = Site.currentDate(),
                companies = $('#companies-list').find('option:eq(0)').val(),
                tags = '';

            $('#date-from').val(dateFrom);
            $('#date-to').val(dateTo);

            getAccountingReport(dateFrom, dateTo, companies, tags, function(success, data) {
                self.templates.accountingReport(data);

                var sum = function(elem) {
                    $('.table-summary').find(elem).text(function(){
                        var total = 0;

                        $(this).parents('.summary').find('td'+elem).each(function(){
                            total += parseInt($(this).text(), 10);
                        })

                        return total;
                    });
                }

                sum('.fee');
                sum('.expense');
                sum('.net');

                $('#all-count').find('span').text($('#all').find('tbody tr').length);
                $('#done-count').find('span').text($('#done').find('tbody tr').length);
                $('#noshows-count').find('span').text($('#noshows').find('tbody tr').length);
                $('#canceled-count').find('span').text($('#canceled').find('tbody tr').length);

                $('.content .table-wrapper').find('table').fixheadertable({
                    height: $('.content .table-wrapper').outerHeight(true),
                    sortable: true,
                    sortType: ['string', 'string', 'string', 'string', 'string', 'integer', 'integer', 'integer'],
                    wrapper: false,
                    resizeCol: true
                });

                Table.fixedHeaderHover('.content .table-wrapper');
            });
        });

        function updateReport() {
            var dateFrom = $('#date-from').val(),
                dateTo = $('#date-to').val(),
                companies = $('#companies-list').val(),
                tags = $('#tags').val();

            $('.tags > div').hide();

            getAccountingReport(dateFrom, dateTo, companies, tags, function(success, data) {
                $('.table-wrapper').each(function(){
                    $(this).find('div:eq(0)').remove();
                    $('.template').clone().removeClass('template').prependTo($(this));

                    if($(this).attr('id') !== 'all') {
                        $(this).find('tbody tr').remove();
                    }
                });

                $('#all-count').parent('li').addClass('active').siblings().removeClass('active');
                $('#all').show().siblings().hide();

                self.templates.accountingReport(data);

                var sum = function(elem) {
                    $('.table-summary').find(elem).text(function(){
                        var total = 0;

                        $(this).parents('.summary').find('td'+elem).each(function(){
                            total += parseInt($(this).text(), 10);
                        });

                        return '$' + total.toString().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
                    });
                }

                sum('.fee');
                sum('.expense');
                sum('.net');

                $('#all-count').find('span').text($('#all').find('tbody tr').length);
                $('#done-count').find('span').text($('#done').find('tbody tr').length);
                $('#noshows-count').find('span').text($('#noshows').find('tbody tr').length);
                $('#canceled-count').find('span').text($('#canceled').find('tbody tr').length);

                Table.fixedHeader('.content .table-wrapper');
                Table.fixedHeaderHover('.content .table-wrapper');

                $('.table-wrapper').each(function(){
                    $('table:hidden').not('.template').show();
                });
            });
        };

        $('#filter-sessions').on('click', function(e){
            updateReport();
        });

        if($.browser.msie) {
            var tmpSelect,
                setTimer;

            function selectTimer(){
                if (tmpSelect !== $('#companies-list').val()) {
                    clearInterval(setTimer);
                    updateReport();
                }
            }

            $(document).on('click', '.filters .dk_toggle', function () {
                tmpSelect = $('#companies-list').val();
                setTimer = setInterval(selectTimer, 200);
            });
        } else {
            $(document).on('click', '.filters .dk_options a', function(e){
                updateReport();
            });
        }

        $('#date-from, #date-to').on('change', function(e){
            updateReport();
        });

        $('#download-report').on('click', function(){
            var dateFrom = $('#date-from').val() || Site.startDate(),
                dateTo = $('#date-to').val() || Site.currentDate(),
                companies = $('#companies-list').val(),
                tags = $('#tags').val();

            downloadAccountingReport(dateFrom, dateTo, companies, tags);
        });

        Overlay.close();
    },

    initOrganization: function(){
        var self = this;

        if (Data.timezone === undefined) {
            self.overlays.setTimezone();
        };

        getData(function(success, data){
            if (data.organizations[0].logo !="") $('.logo').attr('src', data.organizations[0].logo);else $('.logo').hide();
            $('.company-name').text(data.organizations[0].organization_name);
        });

        getSessions(function(success, data) {
            self.templates.organizationSession(data);

            $('tbody tr').each(function(){
                var i = $(this).index();
                $(this).find('.client, .coach').attr('data-index', i);
            });

            $('#all-count').find('span').text($('#all').find('tbody tr').length);
            $('#done-count').find('span').text($('#done').find('tbody tr').length);
            $('#noshows-count').find('span').text($('#noshows').find('tbody tr').length);
            $('#canceled-count').find('span').text($('#canceled').find('tbody tr').length);

            Table.fixedHeader('.content .table-wrapper');
            Table.fixedHeaderHover('.content .table-wrapper');
        });

        getDocumentTemplates(function(success, data) {
            self.documentsData = data;
            self.templates.organizationDocuments(data);
        });

        getStats(function(success, data) {
            self.statsData = data;
            self.templates.organizationStats(data);
        });

        getClients(function(success, data) {
            self.clientsData = data;
            self.templates.organizationClients(data);

            self.overlays.clientsCount('#clients-count');
            self.overlays.clientsSide();
            self.overlays.clientsOverlay();
            // self.overlays.newClient('.new-client');
            // self.overlays.editClient('#clients-side .edit');

            //       $('.overlay').find('.edit').live('click', function(e){
            // 	$(this).parent().next('.editable').editable(function(value, settings) {
            // 	    return(value);
            // 	}, {
            // 		cssclass: 'editable-content',
            // 		type: 'textarea',
            // 		cancel: 'Cancel',
            // 		submit: 'Save Changes',
            // 		tooltip: 'Click to edit...',
            // 		onblur: 'ignore',
            // 		ownCallback: function(){}
            // 	}).click();

            // 	$(this).addClass('active');

            // 	e.preventDefault();
            // });

            // $(document).on('click', '.editable button[type="submit"]', function(){
            // 	var clientId = $(this).parents('.form-content').find('li.active a').attr('data-id'),
            // 		values = {};

            // 		console_log(clientId);

            // 	if ($(this).parents('.focus-area').length) {
            // 		values.focus_area = $(this).siblings('textarea').val();
            // 	} else if ($(this).parents('.success-metrics').length) {
            // 		values.success_metrics = $(this).siblings('textarea').val();
            // 	};

            // 	console_log('');
            // 	console_log('Session ID:');
            // 	console_log(clientId);
            // 	console_log('Sent data values:');
            // 	console_log(values);
            // 	console_log('');

            // 	updateClient(clientId, values, function(success, data){
            // 		console_log(success);
            // 		console_log(data);
            // 	});
            // });

            Data.overlays.clientsReport('.download-report');
        });

        getCoaches(function(success, data) {
            self.coachesData = data;
            self.templates.organizationCoaches(data);

            self.overlays.coachesCount('#coaches-count');
            self.overlays.coachesSide('#coaches-side .item a');
            self.overlays.coachesOverlay();
        });

        Overlay.close();
    },

    initCoach: function(){
        var self = this;

        getData(function(success, data) {
            self.coachData = data;

            if (data.coachs[0].bio_complete === '0') {
                var overlay = '<div class="overlay-bg"></div><div class="overlay-wrapper"><div class="overlay"></div></div>',
                    url = 'modal-coach-profile.html';

                getClients(function(success, data) {
                    self.clientsData = data;
                });

                $('body').append(overlay);
                $('.overlay').load(url, function(){
                    Data.modals.editCoachProfile(Data.coachData.coachs[0]);
                    Data.modals.editCoachProfileList(Data.clientsData);
                    Plugins.initOverlay();
                    $('.overlay-close').remove()
                });
                $('.overlay-wrapper').hide().fadeIn(100);

                $(document).on('click', '#coach-profile-add', function(e){
                    console_log('foo');
                    var coachId = Data.coachData.coachs[0].id,
                        values = {
                            first_name : $('#coach-profile-firstname').val(),
                            last_name : $('#coach-profile-lastname').val(),
                            bio : $('#coach-profile-bio').val(),
                            expertise : $('#coach-profile-credentials').val(),
                            schedule_url : $('#coach-profile-timetrade').val(),
                            phone: $('#coach-profile-phone').val()
                        },
                        validation = Data.validation([
                            '#coach-profile-firstname',
                            '#coach-profile-lastname',
                            '#coach-profile-bio',
                            '#coach-profile-credentials',
                            '#coach-profile-timetrade'
                        ]),
                        urlValidation = new RegExp( '^(http|https|ftp)\://[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(:[a-zA-Z0-9]*)?/?([a-zA-Z0-9\-\._\?\,\'/\\\+&amp;%\$#\=~])*[^\.\,\)\(\s]$' );

                    if (urlValidation.test(values.schedule_url)) {
                        $('#coach-profile-timetrade').removeClass('error');
                    } else {
                        $('#coach-profile-timetrade').addClass('error');
                    }

                    if (validation && urlValidation.test(values.schedule_url)) {
                        updateCoach(coachId, values, function(success, data){
                            // console_log(success);
                            // console_log(data);

                            if ( success === 1 ) {
                                location.reload();
                            } else {
                                if ($('.error-msg').length === 0) {
                                    $('<div class="error-msg"></div>').text(data.message).prependTo(".form-actions");
                                } else {
                                    $('.error-msg').text(data.message);
                                }
                            }
                        });
                    }

                    e.preventDefault();
                });
            }
            else {
                if (Data.timezone === undefined) {
                    self.overlays.setTimezone();
                };

                getSessions(function(success, data) {
                    self.sessionsData = data;
                    self.templates.coachSessions(data);

                    $('tbody tr').each(function(){
                        var i = $(this).index();
                        $(this).find('.client, .coach').attr('data-index', i);
                    });

                    Table.fixedHeader('.content .table-wrapper');
                    Table.fixedHeaderHover('.content .table-wrapper');

                    self.overlays.clientsTable('.client');
                    self.overlays.editSession('.edit-session');
                    self.overlays.deleteSession('.delete-session');
                });

                getClients(function(success, data) {
                    self.clientsData = data;
                    self.overlays.clientsCount('.show-clients');
                    self.overlays.newSession('.new-session');
                    self.overlays.clientsOverlay();
                });

                self.overlays.editCoachProfile('.profile-edit');

                $('.overlay').find('.edit').live('click', function(e){
                    $(this).parent().next('.editable').editable(function(value, settings) {
                        return(value);
                    }, {
                        cssclass: 'editable-content',
                        type: 'textarea',
                        cancel: 'Cancel',
                        submit: 'Save Changes',
                        tooltip: 'Click to edit...',
                        onblur: 'ignore',
                        ownCallback: function(){}
                    }).click();

                    $(this).addClass('active');

                    e.preventDefault();
                });

                $(document).on('click', '.editable button[type="submit"]', function(){
                    var clientId = $(this).parents('.form-content').find('li.active a').attr('data-id'),
                        values = {};

                    //	console_log(clientId);

                    if ($(this).parents('.focus-area').length) {
                        values.focus_area = $(this).siblings('textarea').val();
                    } else if ($(this).parents('.success-metrics').length) {
                        values.success_metrics = $(this).siblings('textarea').val();
                    };

                    //	console_log('');
                    //	console_log('Session ID:');
                    //	console_log(clientId);
                    //	console_log('Sent data values:');
                    //	console_log(values);
                    //	console_log('');

                    updateClient(clientId, values, function(success, data){
                        //		console_log(success);
                        //		console_log(data);

                        getClients(function(success, data) {
                            Data.clientsData = data;
                        });
                    });
                });


                Data.overlays.confirmation();
                Data.overlays.clientsReport('.download-report');
                Overlay.close();
            };
        });
    },

    initClient: function(){
        var self = this;

        if (Data.timezone === undefined) {
            self.overlays.setTimezone();
        };

        getOrganizations(function(success, data){
            if (data.organizations[0].logo !="") $('.logo').attr('src', data.organizations[0].logo);else $('.logo').hide();
        });

        getData(function(success, data) {
            self.templates.clientData(data);
            $('.client-name').text(data.clients[0].first_name + " " + data.clients[0].last_name);
        });

        getSessions(function(success, data) {
            self.templates.clientSessions(data);

            $('.notes-list').find('li').each(function(){
                if ($(this).attr('data-status') === '0') {
                    $(this).remove();
                } else {
                    return;
                }
            });

            if ($('.notes-list').find('li').length === 0) {
                $('.notes-list').append('<li>There is no data available.</li>');
            }

            self.overlays.editSessionNote('.editable button[type="submit"]');

            $(document).on('click', '.notes-list button', function(){
                $(this).parents('li').find('.edit').removeClass('active');
                $(this).parents('li').find('.note-actions').show();
            });
        });

        getDocuments(function(success, data) {
            self.documentsData = data;

            self.templates.clientDocuments(data);
            self.overlays.documentManagement('.documents .title');
        });

        self.overlays.confirmation();
        Overlay.close();
    },

    templates: {
        adminOrganizations: function(data){
            $('.organizations').directives({
                '.item' : { 'organization<-organizations' : {
                    '.title, .title@title' : 'organization.organization_name',
                    '.@data-id' : 'organization.id',
                    '.view@data-id' : 'organization.id'
                }}
            }).render(data);
        },

        adminCoaches: function(data){
            $('.coaches').directives({
                '.item' : { 'coach<-coachs' : {
                    '.title, .title@title' : '#{coach.first_name} #{coach.last_name}',
                    '.@data-id' : 'coach.id'
                }}
            }).render(data);
        },

        adminDocuments: function(data){
            $('.documents').directives({
                '.item' : { 'document<-documentTemplates' : {
                    '.title, .title@title' : 'document.title',
                    '.title@href' : 'document.url',
                    '.title@data-id' : 'document.id',
                    '@class+' : function() {
                        if (this.readonly == '1') {
                            return ' read-only';
                        } else {
                            return ' requires-action';
                        }
                    }
                }}
            }).render(data);
        },

        accountingReport: function(data){
            var cloneItem = function(elem, destination){
                $(elem).parent('tr').clone().appendTo(destination);
            };

            $('#all tbody').directives({
                'tr' : { 'session<-sessions' : {
                    '.client' : '#{session.client_first_name} #{session.client_last_name}',
                    '.coach' : '#{session.coach_first_name} #{session.coach_last_name}',
                    '.date' : function(){
                        var startDate, endDate, __s, __e;
                        if (!!this.session_start_datetime) {
                            __s = this.session_start_datetime.split(" ");
                            if (!!__s) startDate = __s[0];
                        }
                        if (!!this.session_end_datetime) {
                            __e = this.session_end_datetime.split(" ");
                            if (!!__e) endDate = __e[0];
                        }

                        if (startDate === endDate) {
                            return startDate;
                        } else {
                            return startDate + ' - ' + endDate;
                        }
                    },
                    '.time' : function(){
                        var startTime, endTime, __s, __e;
                        if (!!this.session_start_datetime) {
                            __s = this.session_start_datetime.split(" ");
                            if (!!__s) startTime = __s[1] + __s[2];
                        }
                        if (!!this.session_end_datetime) {
                            __e = this.session_end_datetime.split(" ");
                            if (!!__e) endTime = __e[1] + __e[2];
                        }

                        return startTime + ' - ' + endTime;
                    },
                    '.status-static' : function() {
                        if (this.status_code === '1') {
                            return 'Complete';
                        } else if (this.status_code === '2') {
                            return 'No-Show';
                        } else if (this.status_code === '3') {
                            return 'Canceled';
                        }
                    },
                    '.status-static@data-code' : 'session.status_code',
                    '.fee' : function(){
                        return this.bill_rate.toString().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
                    },
                    '.expense' : function(){
                        return this.pay_rate.toString().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
                    },
                    '.net' : function(){
                        return this.net_rate.toString().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
                    }
                }}
            }).render(data);

            // $('.table-summary').directives({
            // 	'.fee' : 'total_billed',
            // 	'.expense' : 'total_paid',
            // 	'.net' : 'total_net',
            // }).render(data.summary);

            $('#all').find('.status-static').each(function(){
                var code = $(this).data('code');

                if (code === 1) {
                    cloneItem(this, '#done tbody');
                } else if (code === 2) {
                    cloneItem(this, '#noshows tbody');
                }
                else if (code === 3) {
                    cloneItem(this, '#canceled tbody');
                }
            });
        },

        accountingOrganizations: function(data){
            $('#companies-list').directives({
                'option' : { 'organization<-organizations' : {
                    '.' : 'organization.organization_name',
                    '.@value' : 'organization.id'
                }}
            }).render(data);
        },

        clientData: function(data){
            var data = data.clients[0];

            $('dl.profile').directives({
                '.name' 	 : '#{first_name} #{last_name}',
                '.employer'  : { 'organization<-organizations' : { '.' : 'organization.organization_name' }},
                '.coach' 	 : { 'coach<-coachs' : { '.' : '#{coach.first_name} #{coach.last_name}'}},
                '.coach_email':{ 'coach<-coachs' : { '.' : '<a href="mailto:#{coach.email}">#{coach.email}</a>'}},
                '.coach_phone':{ 'coach<-coachs' : { '.' : '#{coach.phone}'}},

                '.focusarea' : function(){
                    return '<pre>' + Site.encodeString(this.focus_area) + '</pre>';
                },
                '.metrics' 	 : function(){
                    return '<pre>' + Site.encodeString(this.success_metrics) + '</pre>';
                }
            }).render(data);

            $('.content').directives({
                '.schedule'	 : { 'coach<-coachs' : { '.@href' : 'coach.schedule_url'}},
            }).render(data);
        },

        clientSessions: function(data){
            $('#progress-notes').directives({
                'li' : { 'session<-sessions' : {
                    '.@data-id' : 'session.id',
                    '.@data-status' : 'session.status_code',
                    '.date' : function(){
                        return this.session_end_datetime.split(" ")[0];
                    },
                    '.note-content p' : function(){
                        return '<pre>' + Site.encodeString(session.progress_notes) + '</pre>';
                    },
                    'h4 a@class+' : function() {
                        if (this.progress_notes_approved === '1') {
                            return ' approved';
                        } else {
                            return ' edit';
                        }
                    },
                    'h4 a@title' : function() {
                        if (this.progress_notes_approved === '1') {
                            return '';
                        } else {
                            return 'Edit';
                        }
                    }
                }}
            }).render(data);

            $('#progress-notes').find('li').each(function(){
                if ($(this).find('h4 a').hasClass('approved')) {
                    $(this).find('.note-actions').remove();
                };
            });

            $('#confidential-notes').directives({
                'li' : { 'session<-sessions' : {
                    '.@data-id' : 'session.id',
                    '.@data-status' : 'session.status_code',
                    '.date' : function(){
                        return this.session_end_datetime.split(" ")[0];
                    },
                    '.note-content p' : function(){
                        return '<pre>' + Site.encodeString(session.confidential_notes) + '</pre>';
                    }
                }}
            }).render(data);
        },

        clientDocuments: function(data){
            $('.documents').directives({
                '.item' : { 'document<-documents' : {
                    '.title, .title@title' : 'document.title',
                    '@class+' : function() {
                        if (this.readonly === '1') {
                            return ' read-only';
                        } else if (this.isComplete === '1') {
                            return ' complete';
                        } else {
                            return ' requires-action';
                        }
                    },
                    '.download@href' : function() {
                        if (this.isComplete == '1') {
                            return this.clientDocument_url;
                        } else {
                            return this.documentTemplate_url;
                        }
                    }
                }}
            }).render(data);

            $('.documents').find('.item').each(function(){
                var i = $(this).index();
                $(this).find('.title').attr('data-index', i);
            });
        },

        clientsSessions: function(data){
            $('.session-history').html('');

            if ($('.session-history').find('li').length === 0) {
                $('.session-history').html('<li><strong class="date"></strong> | <span class="time"></span></li>');
            };

            $('.session-history').directives({
                'li' : { 'session<-sessions' : {
                    '.date' : function(){
                        var startDate, endDate, __s, __e;
                        if (!!this.session_start_datetime) {
                            __s = this.session_start_datetime.split(" ");
                            if (!!__s) startDate = __s[0];
                        }
                        if (!!this.session_end_datetime) {
                            __e = this.session_end_datetime.split(" ");
                            if (!!__e) endDate = __e[0];
                        }

                        if (startDate === endDate) {
                            return startDate;
                        } else {
                            return startDate + ' - ' + endDate;
                        }
                    },
                    '.time' : function(){
                        var startTime, endTime, __s, __e;
                        if (!!this.session_start_datetime) {
                            __s = this.session_start_datetime.split(" ");
                            if (!!__s) startTime = __s[1] + __s[2];
                        }
                        if (!!this.session_end_datetime) {
                            __e = this.session_end_datetime.split(" ");
                            if (!!__e) endTime = __e[1] + __e[2];
                        }

                        return startTime + ' - ' + endTime;
                    },
                }}
            }).render(data);
        },

        clientsDocuments: function(data){
            $('#modal-clients .documents').html('');

            if ($('#modal-clients .documents').find('li').length === 0) {
                $('#modal-clients .documents').html('<li class="item"><a href="#" title="">foo</a></li>');
            };

            $('#modal-clients .documents').directives({
                '.item' : { 'document<-documents' : {
                    'a, a@title' : 'document.title',
                    'a@data-url' : 'document.clientDocument_url',
                    '@class+' : function() {
                        if (this.readonly === '1') {
                            return ' read-only';
                        } else if (this.isComplete === '1') {
                            return ' complete';
                        } else {
                            return ' requires-action';
                        }
                    },
                    'a@href' : function() {
                        if (this.isComplete === '1') {
                            return this.clientDocument_url;
                        } else {
                            return this.documentTemplate_url;
                        }
                    }
                }}
            }).render(data);
        },

        clientsOptions: function(data){
            $('#new-session-client').directives({
                'option' : { 'client<-clients' : {
                    '.' : '#{client.first_name} #{client.last_name}',
                    '.@data-id' : 'client.id',
                    '.@value' : 'client.id',
                }}
            }).render(data);
        },

        coachSessions: function(data){
            $('.content tbody').directives({
                'tr' : { 'session<-sessions' : {
                    '.@data-id' : 'session.id',
                    '.client, .client@title' : '#{session.client_first_name} #{session.client_last_name}',
                    '.client@data-id' : 'session.client_id',
                    '.organization' : 'session.client_organization',
                    '.date' : function(){
                        var startDate, endDate, __s, __e;
                        if (!!this.session_start_datetime) {
                            __s = this.session_start_datetime.split(" ");
                            if (!!__s) startDate = __s[0];
                        }
                        if (!!this.session_end_datetime) {
                            __e = this.session_end_datetime.split(" ");
                            if (!!__e) endDate = __e[0];
                        }



                        if (startDate === endDate) {
                            return startDate;
                        } else {
                            return startDate + ' - ' + endDate;
                        }
                    },
                    '.time' : function(){
                        var startTime, endTime, __s, __e;
                        if (!!this.session_start_datetime) {
                            __s = this.session_start_datetime.split(" ");
                            if (!!__s) startTime = __s[1] + __s[2];
                        }
                        if (!!this.session_end_datetime) {
                            __e = this.session_end_datetime.split(" ");
                            if (!!__e) endTime = __e[1] + __e[2];
                        }

                        return startTime + ' - ' + endTime;
                    },
                    '.status' : function() {
                        if (this.status_code === '1') {
                            return 'Complete';
                        } else if (this.status_code === '2') {
                            return 'No-Show';
                        } else if (this.status_code === '3') {
                            return 'Canceled';
                        }
                    }
                }}
            }).render(data);

            $('tbody tr').each(function(){
                var i = $(this).index();
                $(this).find('.status').siblings('a').attr('data-index', i);
            });
        },

        coachOrganizations: function(data){
            $('#modal-new-coach .companies').directives({
                '.item' : { 'organization<-organizations' : {
                    'span, span@title' : 'organization.organization_name',
                    'input@value' : 'organization.id'
                }}
            }).render(data);
        },

        documentOrganizations: function(data){
            $('#modal-new-document .companies').directives({
                '.item' : { 'organization<-organizations' : {
                    'span, span@title' : 'organization.organization_name',
                    'input@value' : 'organization.id'
                }}
            }).render(data);
        },

        organizationSession: function(data){
            var cloneItem = function(elem, destination){
                $(elem).parent('tr').clone().appendTo(destination);
            };

            $('#all tbody').directives({
                'tr' : { 'session<-sessions' : {
                    '.@data-id' : 'session.id',
                    '.client, .client' : '#{session.client_first_name} #{session.client_last_name}',
                    '.client@data-id' : 'session.client_id',
                    '.coach, .coach@title' : '#{session.coach_first_name} #{session.coach_last_name}',
                    '.date' : function(){
                        var startDate, endDate, __s, __e;
                        if (!!this.session_start_datetime) {
                            __s = this.session_start_datetime.split(" ");
                            if (!!__s) startDate = __s[0];
                        }
                        if (!!this.session_end_datetime) {
                            __e = this.session_end_datetime.split(" ");
                            if (!!__e) endDate = __e[0];
                        }

                        if (startDate === endDate) {
                            return startDate;
                        } else {
                            return startDate + ' - ' + endDate;
                        }
                    },
                    '.time' : function(){
                        var startTime, endTime, __s, __e;
                        if (!!this.session_start_datetime) {
                            __s = this.session_start_datetime.split(" ");
                            if (!!__s) startTime = __s[1] + __s[2];
                        }
                        if (!!this.session_end_datetime) {
                            __e = this.session_end_datetime.split(" ");
                            if (!!__e) endTime = __e[1] + __e[2];
                        }

                        return startTime + ' - ' + endTime;
                    },
                    '.status-static@data-code' : 'session.status_code',
                    '.status-static span' : function() {
                        if (this.status_code === '1') {
                            return 'Complete';
                        } else if (this.status_code === '2') {
                            return 'No-Show';
                        } else if (this.status_code === '3') {
                            return 'Canceled';
                        }
                    }
                }}
            }).render(data);

            $('#all').find('tbody').find('tr').each(function(){
                var i = $(this).index();
                $(this).attr('data-index', i).find('.client, .coach').attr('data-index', i);
            });

            $('#all').find('.status-static').each(function(){
                var code = $(this).data('code');

                if (code === 1) {
                    cloneItem(this, '#done tbody');
                } else if (code === 2) {
                    cloneItem(this, '#noshows tbody');
                } else if (code === 3) {
                    cloneItem(this, '#canceled tbody');
                }
            });
        },

        organizationDocuments: function(data){
            $('.documents').directives({
                '.item' : { 'document<-documentTemplates' : {
                    '.title, .title@title' : 'document.title',
                    '.title@href' : 'document.url',
                    '@class+' : function() {
                        if (this.readonly == 1) {
                            return ' read-only';
                        } else {
                            return ' requires-action';
                        }
                    }
                }}
            }).render(data);
        },

        organizationStats: function(data){
            $('#metric-data').directives({
                '#clients-count' : 'counts.client',
                '#coaches-count' : 'counts.coach',
                '#budget-total span' : function(){
                    return (this.budget.total || 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") + '.00'
                },
                '#budget-used span' : function(){
                    return (this.budget.used || 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") + '.00'
                },
                '#budget-remaining strong' : function(){
                    return (this.budget.balance || 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") + '.00'
                },
                '#progress-selected' : '#{progress.milestone_1}%',
                '#progress-first' : '#{progress.milestone_2}%',
                '#progress-half' : '#{progress.milestone_3}%',
                '#progress-all' : '#{progress.milestone_4}%'
            }).render(data.stats);
        },

        organizationClients: function(data){
            $('#clients-side ul.styled-list').directives({
                '.item' : { 'client<-clients' : {
                    '.title, .title@title' : '#{client.first_name} #{client.last_name}',
                    '.title@data-id' : 'client.id',
                    '.@data-id' : 'client.id'
                }}
            }).render(data);
        },

        organizationCoaches: function(data){
            $('#coaches-side ul.styled-list').directives({
                '.item' : { 'coach<-coachs' : {
                    '.title, .title@title' : '#{coach.first_name} #{coach.last_name}',
                    '.@data-id' : 'coach.id'
                }}
            }).render(data);
        },

        newClientCoaches: function(data){

            $('#new-client-coach').directives({
                'option' : { 'coach<-coachs' : {
                    '.' : '#{coach.first_name} #{coach.last_name}',
                    '.@value' : 'coach.id'
                }}
            }).render(data);
            $('#new-client-coach').append("<option value='null' selected>No Coach Selected</option>");
        }
    },

    validation: function(data) {
        var errors = 0,
            mailValidation = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

        $.each(data, function(i){
            if ($(data[i]).attr('type') === "email") {
                if ( !mailValidation.test($(data[i]).val()) || $(data[i]).val() === '' ) {
                    errors++;
                    $(data[i]).addClass('error');
                } else {
                    $(data[i]).removeClass('error');
                };
            } else if ($(data[i]).is('select')) {
                if ($(data[i]).val() === '' || $(data[i]).val() === 'null') {
                    errors++;
                    $(data[i]).siblings('.dk_container').addClass('error');
                } else {
                    $(data[i]).siblings('.dk_container').removeClass('error');
                };
            } else {
                if ($(data[i]).val() === '' || $(data[i]).val() === $(data[i]).attr('placeholder')) {
                    errors++;
                    $(data[i]).addClass('error');
                } else {
                    $(data[i]).removeClass('error');
                };
            };
        });

        if (errors > 0) {
            if (!$('.error-msg').length) {
                $('.form-actions').prepend('<p class="error-msg">Please fill in the form.</p>');
            };

            if ($('.overflow').length) {
                $('.overflow').scrollTop($('.error').offset().top);
            };

            return false;
        } else {
            return true;
        };
    },

    modals: {
        clientsList: function(data){
            $('#modal-clients').directives({
                '.people li' : { 'client<-clients' : {
                    'a' : '#{client.first_name} #{client.last_name}',
                    'a@data-id' : 'client.id',
                    'a@data-date' : function(){
                        return this.start_date.split(" ")[0];
                    }
                }}
            }).render(data);
        },

        clientsData: function(data){
            $('#modal-clients .wrapper').directives({
                '.name' : '#{first_name} #{last_name}',
                '.coach' : '#{coach_first_name} #{coach_last_name}',
                '.level' : 'organization_level',
                '.focus-area' : function(){
                    return Site.encodeString(this.focus_area);
                },
                '.success-metrics' : function(){
                    return Site.encodeString(this.success_metrics);
                },
                '.email' : '<a href=="mailto:#{email}">#{email}</a>',
                '.phone' : '#{phone}'
            }).render(data);
        },

        coachesList: function(data){
            $('#modal-coaches').directives({
                '.people li' : { 'coach<-coachs' : {
                    'a' : '#{coach.first_name} #{coach.last_name}'
                }}
            }).render(data);
        },

        coachesData: function(data){
            $('#modal-coaches .wrapper').directives({
                '.name' : '#{first_name} #{last_name}',
                '.biography' : function(){
                    return Site.encodeString(this.bio);
                },
                '.expertise' : function(){
                    return Site.encodeString(this.expertise);
                },

            }).render(data);
        },

        editSession: function(data){
            if ($('#new-session-payrate').length == 0) {
                $('#modal-new-session').directives({
                    '#new-session-client@data-code' : 'client_id',
                    '#new-session-date@value' : function(){
                        var startDate, __s;
                            if (!!this.session_start_datetime) {
                                __s = this.session_start_datetime.split(" ");
                                if (!!__s) startDate = __s[0];
                            }

                        return startDate;
                    },
                    '#new-session-time-from@value' : function(){
                        var startDate, __s;
                            if (!!this.session_start_datetime) {
                                __s = this.session_start_datetime.split(" ");
                                if (!!__s) startDate = __s[1] + ' ' + __s[2];
                            }

                        return startDate;
                    },
                    '#new-session-time-to@value' : 'duration',
                    '#new-session-type@data-code' : 'status_code',
                    '#new-session-report' : function(){
                        return Site.encodeString(this.progress_notes);
                    },
                    '#new-session-notes' : function(){
                        return Site.encodeString(this.confidential_notes);
                    }
                }).render(data);
            } else {
                $('#modal-new-session').directives({
                    '#new-session-client@data-code' : 'client_id',
                    '#new-session-date@value' : function(){
                        var startDate, __s;
                        if (!!this.session_start_datetime) {
                            __s = this.session_start_datetime.split(" ");
                            if (!!__s) startDate = __s[0];
                        }

                        return startDate;
                    },
                    '#new-session-time-from@value' : function(){
                        var startDate, __s;
                        if (!!this.session_start_datetime) {
                            __s = this.session_start_datetime.split(" ");
                            if (!!__s) startDate = __s[1] + ' ' + __s[2];
                        }

                        return startDate;
                    },
                    '#new-session-time-to@value' : 'duration',
                    '#new-session-type@data-code' : 'status_code',
                    '#new-session-report' : function(){
                        return Site.encodeString(this.progress_notes);
                    },
                    '#new-session-notes' : function(){
                        return Site.encodeString(this.confidential_notes);
                    },
                    '#new-session-payrate@value' : 'pay_rate',
                    '#new-session-billrate@value': 'bill_rate'
                }).render(data);
            }

            var optionClient = 'option[value="' + $('#new-session-client').data('code') + '"]',
                optionSession = 'option[value="' + $('#new-session-type').data('code') + '"]';

                console_log(optionClient);
                console_log($('#new-session-client').find(optionClient));

            $('#new-session-client').find(optionClient).attr("selected", true);
            $('#new-session-type').find(optionSession).attr("selected", true);
        },

        documentManagement: function(data){
            $('#modal-document-management').directives({
                '.filename span' : 'title',
                '#document_id@value' : 'documentTemplate_id',
                '.filename span@class' : function() {
                    if (this.readonly == '1') {
                        return ' read-only';
                    } else if (this.isComplete == '1') {
                        return ' complete';
                    } else {
                        return ' requires-action';
                    }
                },
                '#download-file@href' : function() {
                    if (this.isComplete == '1') {
                        return this.clientDocument_url;
                    } else {
                        return this.documentTemplate_url;
                    }
                }
            }).render(data);
        },

        documentsList: function(data){
            $('.companies').directives({
                'li' : { 'organization<-organizations' : {
                    'input@value' : 'organization.id',
                    'span' : 'organization.organization_name'
                }}
            }).render(data);
        },

        editClient: function(data){
            $('#modal-new-client').directives({
                '#new-client-email@value' : 'email',
                '#new-client-firstname@value' : 'first_name',
                '#new-client-lastname@value' : 'last_name',
                '#new-client-focus' : function(){
                    return Site.encodeString(this.focus_area);
                },
                '#new-client-sessions@value' : 'sessions_allotment',
                '#new-client-date@value' : function(){
                    return this.start_date.split(" ")[0];
                },
                '#new-client-success' : function(){
                    return Site.encodeString(this.success_metrics);
                },
                '#new-client-level@value' : 'organization_level',
                '#new-client-tags@value'  : 'tags',
                '#new-client-other@value' : 'sessions_frequency_other',
                '#new-client-billrate@value' : 'bill_rate'
            }).render(data);
        },

        coachProfile: function(data){
            $('#modal-new-coach').directives({
                '#new-coach-firstname@value' : 'first_name',
                '#new-coach-lastname@value' : 'last_name',
                '#new-coach-email@value' : 'email',
                '#new-coach-bio' : function(){
                    return Site.encodeString(this.bio);
                },
                '#new-coach-credentials' : function(){
                    return Site.encodeString(this.expertise);
                },
                '#new-coach-timetrade@value' : 'schedule_url',
                '#new-coach-payrate@value' : 'pay_rate'
            }).render(data);
        },

        editCoachProfile: function(data){
            $('#modal-coach-profile').directives({
                '#coach-profile-firstname@value' : 'first_name',
                '#coach-profile-lastname@value' : 'last_name',
                '#coach-profile-bio' : function(){
                    return Site.encodeString(this.bio);
                },
                '#coach-profile-credentials' : function(){
                    return Site.encodeString(this.expertise);
                },
                '#coach-profile-timetrade@value' : 'schedule_url',
                '#coach-profile-phone@value' : 'phone'
            }).render(data);
        },

        editCoachProfileList: function(data){
            $('#table-clients').directives({
                'tbody tr' : { 'client<-clients' : {
                    '.name' : '#{client.first_name} #{client.last_name}',
                    '.organization' : 'client.organization_name'
                }}
            }).render(data);
        },

        organizationProfile: function(data){
            $('#modal-new-organization').directives({
                '#new-organization-firstname@value' : 'first_name',
                '#new-organization-lastname@value' : 'last_name',
                '#new-organization-email@value' : 'email',
                '#new-organization-name@value' : 'organization_name',
                '#new-organization-street@value' : 'addr_street',
                '#new-organization-city@value' : 'addr_city',
                '#new-organization-state@data-state' : 'addr_state',
                '#new-organization-zip@value' : 'addr_zip',
                '#new-organization-info' : function(){
                    return Site.encodeString(this.notes);
                },
                '#new-organization-budget@value' : 'budget',
                '#new-organization-phone@value' : 'phone'
            }).render(data);
        }
    },

    overlays: {
        setTimezone: function(){
            var overlay = '<div class="overlay-bg"></div><div class="overlay-wrapper"><div class="overlay"></div></div>',
                url = 'modal-timezone.html';

            $('body').append(overlay).css('overflow', 'hidden');



            $('.overlay').load(url, function(){
                Plugins.initOverlay();
                $('.overlay-close').remove()

                $(document).on('click', '#set-timezone', function(){
                    var value = $('#modal-timezone').find('select').val(),
                        daylight = $('#daylight-savings').is(':checked');

                    updateTimezone(value, daylight, function(success, data){
                        console_log('code: ', success);
                        console_log('data: ', data);

                        location.reload();
                    });
                });
            });
            $('.overlay-wrapper').hide().fadeIn(100);
        },

        editTimezone: function(elem){
            $(document).on('click', elem, function(e){
                Overlay.open.call(this, function(){
                    var currentTimezone = 'option[value="' + Data.timezone + '"]';

                    $('#modal-timezone').find(currentTimezone).attr('selected', true);
                    Data.daylight === '1' && $('#daylight-savings').attr('checked', true);

                    Plugins.initOverlay();
                });

                e.preventDefault();
            });

            $(document).on('click', '#set-timezone', function(){
                var value = $('#modal-timezone').find('select').val(),
                    daylight = $('#daylight-savings').is(':checked');

                    console_log(value);
                    
                updateTimezone(value, daylight, function(success, data){
                    console_log('code: ', success);
                    console_log('data: ', data);

                    location.reload();
                });
            });
        },

        clientsCount: function(elem){
            if (Data.clientsData.clients.length === 0) {
                $(elem).addClass('disabled');
                $(document).on('click', elem, function(e){
                    e.preventDefault();
                });
            } else {
                $(document).on('click', elem, function(e){
                    Overlay.open.call(this, function(){
                        if ($('body').hasClass('organization')) {
                            $('.overlay').find('.edit').remove();
                        }

                        Data.modals.clientsList(Data.clientsData);
                        Data.modals.clientsData(Data.clientsData.clients[0]);
                        $('#modal-clients').find('li').eq(0).addClass('active');

                        var id = $('#modal-clients').find('li').eq(0).find('a').attr('data-id');

                        getSessionsForClient(id, function(success, data){
                            Data.templates.clientsSessions(data);
                        });

                        getDocumentsForClient(id, function(success, data){
                            Data.templates.clientsDocuments(data);
                        });
                    });

                    e.preventDefault();
                });
            }
        },

        clientsSide: function(){
            $(document).on('click', '#clients-side .title', function(e){
                var i = $(this).parents('.item').index(),
                    self = this;

                Overlay.open.call(this, function(){
                    if ($('body').hasClass('organization')) {
                        $('.overlay').find('.edit').remove();
                    }

                    Data.modals.clientsList(Data.clientsData);
                    Data.modals.clientsData(Data.clientsData.clients[i]);
                    $('#modal-clients').find('li').eq(i).addClass('active');

                    Data.overlays.clientsDocuments.call(self);
                    Data.overlays.clientsSessions.call(self);
                });

                e.preventDefault();
            });
        },

        clientsTable: function(elem){
            $(document).on('click', elem, function(e){
                var id = $(this).attr('data-id');

                Overlay.open.call(this, function(){
                    Data.modals.clientsList(Data.clientsData);
                    Data.modals.clientsData(Data.clientsData.clients[0]);
                    $('#modal-clients').find('a[data-id="' + id + '"]').parent('li').click();
                });

                Data.overlays.clientsSessions.call(this);
                Data.overlays.clientsDocuments.call(this);

                e.preventDefault();
            });
        },

        clientsOverlay: function(){
            $(document).on('click', '#modal-clients .people li', function(e){
                var i = $(this).index();

                $(this).addClass('active').siblings().removeClass('active');
                Data.modals.clientsData(Data.clientsData.clients[i]);

                Data.overlays.clientsSessions.call(this);
                Data.overlays.clientsDocuments.call(this);

                e.preventDefault();
            });
        },

        clientsReport: function(elem){
            $(document).on('click', elem, function(e){
                var ctx = $('#modal-clients .active').find('a'),
                    id = ctx.attr('data-id'),
                    start = ctx.attr('data-date'),
                    end = Site.currentDate();

                downloadProgressReport(id, start, end);

                e.preventDefault;
            });
        },

        clientsSessions: function(){
            var id = $(this).is('a') ? $(this).attr('data-id') : $(this).find('a').attr('data-id');

            getSessionsForClient(id, function(success, data){
                Data.templates.clientsSessions(data);
            });
        },

        clientsDocuments: function(){
            var id = $(this).is('a') ? $(this).attr('data-id') : $(this).find('a').attr('data-id');

            getDocumentsForClient(id, function(success, data){
                Data.templates.clientsDocuments(data);
            });
        },

        coachesCount: function(elem){
            if (Data.coachesData.coachs.length === 0) {
                $(elem).addClass('disabled');
                $(document).on('click', elem, function(e){
                    e.preventDefault();
                });
            } else {
                $(document).on('click', elem, function(e){
                    Overlay.open.call(this, function(){
                        Data.modals.coachesList(Data.coachesData);
                        Data.modals.coachesData(Data.coachesData.coachs[0]);
                        $('#modal-coaches').find('li').eq(0).addClass('active');
                    });

                    e.preventDefault();
                });
            }
        },

        coachesSide: function(elem){
            $(document).on('click', elem, function(e){
                var i = $(this).parents('.item').index();

                Overlay.open.call(this, function(){
                    Data.modals.coachesList(Data.coachesData);
                    Data.modals.coachesData(Data.coachesData.coachs[i]);
                    $('#modal-coaches').find('li').eq(i).addClass('active');
                });

                e.preventDefault();
            });
        },

        coachesOverlay: function(){
            $(document).on('click', '#modal-coaches .people li', function(e){
                var i = $(this).index();

                $(this).addClass('active').siblings().removeClass('active');
                Data.modals.coachesData(Data.coachesData.coachs[i]);

                e.preventDefault();
            });
        },

        coachesTable: function(){
            $(document).on('click', '.sessions .coach', function(e){
                var i = $(this).attr('data-index');

                Overlay.open.call(this, function(){
                    Data.modals.coachesList(Data.coachesData);
                    Data.modals.coachesData(Data.coachesData.coachs[0]);
                    $('#modal-coaches').find('li').eq(i).addClass('active');
                });

                e.preventDefault();
            });
        },

        newSession: function(elem) {
            if (Data.clientsData.clients.length === 0) {
                $(elem).addClass('disabled');
                $(document).on('click', elem, function(e){
                    e.preventDefault();
                });
            } else {
                $(document).on('click', elem, function(e){
                    Overlay.open.call(this, function(){
                        Data.templates.clientsOptions(Data.clientsData);
                        Plugins.initOverlay();
                    });

                    e.preventDefault();
                });
            }

            $(document).on('click', '#new-session-add', function(e){
                var d = new Date($('#new-session-date').val()+' '+$('#new-session-time-from').val());
                console_log("Edit session save click");
                var	values = {
                        client_id: $('#new-session-client option:selected').attr('data-id'),
                        session_start_datetime : $('#new-session-date').val()+' '+$('#new-session-time-from').val(),
                        duration : $('#new-session-time-to').val(),
                        status_code : $('#new-session-type').val(),
                        progress_notes : $('#new-session-report').val(),
                        confidential_notes : $('#new-session-notes').val()
                    };
                if ($('#new-session-payrate').length>0) {
                    values["pay_rate"] = $('#new-session-payrate').val();
                    values["bill_rate"] = $('#new-session-billrate').val();
                }
                var    validation = Data.validation([
                        '#new-session-date',
                        '#new-session-time-from',
                        '#new-session-time-to',
                        '#new-session-report'
                    ]);

                //	console_log('');
                //	console_log('Sent data values:');
                //	console_log(values);
                //	console_log('');

                if (validation) {
                    addSession(values, function(success, data){
                        //		console_log(success);
                        //		console_log(data);
                        if ( success === 1 ) {
                            var orgId = window.location.search.substring(4);

                            if (orgId !== '') {
                                getSessionsForOrganization(orgId, function(success, data) {
                                    $('.sessions').find('.body').find('tr').not(':eq(0)').remove();

                                    Data.sessionsData = data;
                                    Data.templates.organizationSession(data);

                                    $('tbody tr').each(function(){
                                        var i = $(this).index();
                                        $(this).find('.client, .coach').attr('data-index', i);
                                    });

                                    $('.sessions .body').find('tr:odd').addClass('ui-state-active');

                                    $('#all-count').find('span').text($('#all').find('tbody tr').length);
                                    $('#done-count').find('span').text($('#done').find('tbody tr').length);
                                    $('#noshows-count').find('span').text($('#noshows').find('tbody tr').length);
                                    $('#canceled-count').find('span').text($('#canceled').find('tbody tr').length);
                                });
                            } else {
                                getSessions(function(success, data) {
                                    $('.sessions').find('.body').find('tr').not(':eq(0)').remove();

                                    Data.sessionsData = data;
                                    Data.templates.coachSessions(data);

                                    $('tbody tr').each(function(){
                                        var i = $(this).index();
                                        $(this).find('.client, .coach').attr('data-index', i);
                                    });

                                    $('.sessions .body').find('tr:odd').addClass('ui-state-active');

                                    Overlay.closeTrigger();
                                });
                            }

                            Overlay.closeTrigger();
                        } else {
                            if ($('.error-msg').length === 0) {
                                $('<div class="error-msg"></div>').text(data.message).prependTo(".form-actions");
                            } else {
                                $('.error-msg').text(data.message);
                            }
                        }
                    });
                }

                e.preventDefault();
            });
        },

        editSession: function(elem) {
            $(document).on('click', elem, function(e){

                Data.sessionIndex = $(this).attr('data-index');

                Overlay.open.call(this, function(){
                    Data.templates.clientsOptions(Data.clientsData);
                    Data.modals.editSession(Data.sessionsData.sessions[Data.sessionIndex]);

                    Plugins.initOverlay();
                });

                e.preventDefault();
            });

            $(document).on('click', '#new-session-edit', function(){

                var sessionId = Data.sessionsData.sessions[Data.sessionIndex].id,
                    values = {
                        client_id: $('#new-session-client option:selected').attr('data-id'),
                        session_start_datetime : $('#new-session-date').val()+' '+$('#new-session-time-from').val(),
                        duration : $('#new-session-time-to').val(),
                        status_code : $('#new-session-type').val(),
                        progress_notes : $('#new-session-report').val(),
                        confidential_notes : $('#new-session-notes').val()
                    };

                    if ($('#new-session-payrate').length>0) {
                        values["pay_rate"] = $('#new-session-payrate').val();
                        values["bill_rate"] = $('#new-session-billrate').val();
                    }

                //	console_log('');
                //	console_log('Session ID:');
                //	console_log(sessionId);
                //	console_log('Sent data values:');
                //	console_log(values);
                //	console_log('');

                updateSession(sessionId, values, function(success, data){
                    //		console_log(success);
                    //		console_log(data);

                    if ( success === 1 ) {
                        getSessions(function(success, data) {
                            $('.sessions').find('.body').find('tr').not(':eq(0)').remove();

                            Data.sessionsData = data;
                            Data.templates.coachSessions(data);

                            $('tbody tr').each(function(){
                                var i = $(this).index();
                                $(this).find('.client, .coach').attr('data-index', i);
                            });

                            $('.sessions .body').find('tr:odd').addClass('ui-state-active');

                            Overlay.closeTrigger();
                        });
                    } else {
                        if ($('.error-msg').length === 0) {
                            $('<div class="error-msg"></div>').text(data.message).prependTo(".form-actions");
                        } else {
                            $('.error-msg').text(data.message);
                        }
                    }
                });
            });
        },

        editOrganizationSession: function(elem) {
            $(document).on('click', elem, function(e){
                Data.orgId = window.location.search.substring(4);
                Data.sessionIndex = $(this).parents('tr').data('index');

                Overlay.open.call(this, function(){
                    Data.templates.clientsOptions(Data.clientsData);
                    Data.modals.editSession(Data.sessionsData.sessions[Data.sessionIndex]);

                    Plugins.initOverlay();
                });

                e.preventDefault();
            });

            $(document).on('click', '#new-session-edit', function(){

                var sessionId = Data.sessionsData.sessions[Data.sessionIndex].id,
                    values = {
                        client_id: $('#new-session-client option:selected').attr('data-id'),
                        session_start_datetime : $('#new-session-date').val()+' '+$('#new-session-time-from').val(),
                        duration : $('#new-session-time-to').val(),
                        status_code : $('#new-session-type').val(),
                        progress_notes : $('#new-session-report').val(),
                        confidential_notes : $('#new-session-notes').val()
                    };

                if ($('#new-session-payrate').length>0) {
                    values["pay_rate"] = $('#new-session-payrate').val();
                    values["bill_rate"] = $('#new-session-billrate').val();
                }
                //  console_log('');
                //  console_log('Session ID:');
                //  console_log(sessionId);
                //  console_log('Sent data values:');
                //  console_log(values);
                //  console_log('');

                updateSession(sessionId, values, function(success, data){
                    //      console_log(success);
                    //      console_log(data);

                    if ( success === 1 ) {
                        getSessionsForOrganization(Data.orgId, function(success, data) {
                            $('.sessions').find('.body').find('tr').not(':eq(0)').remove();

                            Data.sessionsData = data;
                            Data.templates.organizationSession(data);

                            $('.sessions .body').find('tr:odd').addClass('ui-state-active');

                            $('#all-count').find('span').text($('#all').find('tbody tr').length);
                            $('#done-count').find('span').text($('#done').find('tbody tr').length);
                            $('#noshows-count').find('span').text($('#noshows').find('tbody tr').length);
                            $('#canceled-count').find('span').text($('#canceled').find('tbody tr').length);

                            Overlay.closeTrigger();
                        });
                    } else {
                        if ($('.error-msg').length === 0) {
                            $('<div class="error-msg"></div>').text(data.message).prependTo(".form-actions");
                        } else {
                            $('.error-msg').text(data.message);
                        }
                    }
                });
            });
        },

        editSessionNote: function(elem) {
            $(document).on('click', elem, function(){
                var sessionId = $(this).parents('li').attr('data-id'),
                    values = {};

                if ($(this).parents('#progress-notes').length) {
                    values.progress_notes = $(this).siblings('textarea').val();
                } else if ($(this).parents('#confidential-notes').length) {
                    values.confidential_notes = $(this).siblings('textarea').val();
                };

                console_log('');
                console_log('Session ID:');
                console_log(sessionId);
                console_log('Sent data values:');
                //	console_log(values);
                console_log('');

                updateSession(sessionId, values, function(success, data){
                    //		console_log(success);
                    //		console_log(data);
                });
            });

            $(document).on('click', '.approve-note', function(e){
                Data.sessionId = $(this).parents('li').attr('data-id');
                Data.callValues = {
                    progress_notes_approved : 1
                };
                Data.confirm = 'ctiSessionEditPermanent';

                console_log('');
                console_log('Session ID:');
                console_log(Data.sessionId);
                console_log('Sent data values:');
                //	console_log(Data.callValues);
                console_log('');

                if (cookie.get(Data.confirm) === 'perm') {
                    updateSession(Data.sessionId, Data.callValues, function(success, data){
                        //			console_log(success);
                        //			console_log(data);

                        if ( success === 1 ) { location.reload(); }
                    });


                } else {
                    Overlay.open.call(this, function(){
                        $('.message').text('Are you sure you want to approve this note?');
                    });
                }

                e.preventDefault();
            });
        },

        deleteSession: function(elem) {
            $(document).on('click', elem, function(e){
                Data.item = $(this).parents('tr');
                Data.sessionId = $(this).parents('tr').attr('data-id');
                Data.confirm = 'ctiSessionDeletePermanent';

                console_log('');
                console_log('Session ID:');
                console_log(Data.sessionId);
                console_log('');

                if (cookie.get(Data.confirm) === 'perm') {
                    deleteSession(Data.sessionId, function(success, data){
                        console_log(success);
                        console_log(data);
                        
                        if ( success === 1 ) {
                            Site.removeItem(Data.sessionsData.sessions);
                            $('.sessions').find('.body').find('tr').removeClass('ui-state-active').filter(':odd').addClass('ui-state-active');
                        }
                    });
                } else {
                    Overlay.open.call(this, function(){
                        $('.message').text('Are you sure you want to delete this session?');
                    });
                }

                e.preventDefault();
            });
        },

        deleteOrganizationSession: function(elem) {
            $(document).on('click', elem, function(e){
                Data.item = $(this).parents('tr');
                Data.sessionId = $(this).parents('tr').attr('data-id');
                Data.confirm = 'ctiOrganizationSessionDeletePermanent';
                Data.orgId = window.location.search.substring(4);

                console_log('');
                console_log('Session ID:');
                console_log(Data.sessionId);
                console_log('');

                if (cookie.get(Data.confirm) === 'perm') {
                    deleteSession(Data.sessionId, function(success, data){
                        console_log(success);
                        console_log(data);
                        
                        if ( success === 1 ) {
                            getSessionsForOrganization(Data.orgId, function(success, data) {
                                $('.sessions').find('.body').find('tr').not(':eq(0)').remove();

                                Data.sessionsData = data;
                                Data.templates.organizationSession(data);

                                $('.sessions .body').find('tr:odd').addClass('ui-state-active');

                                $('#all-count').find('span').text($('#all').find('tbody tr').length);
                                $('#done-count').find('span').text($('#done').find('tbody tr').length);
                                $('#noshows-count').find('span').text($('#noshows').find('tbody tr').length);
                                $('#canceled-count').find('span').text($('#canceled').find('tbody tr').length);

                                Overlay.closeTrigger();
                            });
                        }
                    });
                } else {
                    Overlay.open.call(this, function(){
                        $('.message').text('Are you sure you want to delete this session?');
                    });
                }

                e.preventDefault();
            });
        },

        documentManagement: function(elem) {
            $(document).on('click', elem, function(e){
                var i = $(this).parents('li').index();

                Overlay.open.call(this, function(){
                    Data.modals.documentManagement(Data.documentsData.documents[i]);

                    if (Data.documentsData.documents[i].readonly === '1') {
                        $('.document-upload').remove();
                    } else {
                        var uploader = new qq.FileUploader({
                            element: $('#new-document-upload')[0],
                            uploadButtonText: 'choose',
                            sizeLimit: 8*1024*1024,
                            action: SERVER_URI + '/upload',
                            autoUpload: true,
                            params: {
                                document_id: $('#document_id').val()
                            },
                            multiple: false,
                            onSubmit: function(id, filename) {
                                $('.qq-upload-list').css('width', '-=5px');
                                $('.qq-upload-button').css('width', '+=20px').text('uploading');
                            },
                            onComplete: function(id, fileName, responseJSON) {
                                if (responseJSON.success === "true") {
                                    $('.form-content').hide().siblings('.success').show(function(){
                                        setTimeout(function(){
                                            location.reload()
                                        }, 2000);
                                    });
                                } else {
                                    $('.form-content').hide().siblings('.error').show(function(){
                                        setTimeout(function(){
                                            location.reload()
                                        }, 2000);
                                    });
                                }
                            }
                        });
                    }

                    Plugins.initOverlay();
                });

                e.preventDefault();
            });
        },

        deleteDocument: function(elem) {
            $(document).on('click', elem, function(e){
                Data.item = $(this).parents('.item');
                Data.documentId = $(this).parents('.item').find('.title').attr('data-id');
                Data.confirm = 'ctiDocumentDeletePermanent';

                console_log('');
                console_log('Document ID:');
                console_log(Data.documentId);
                console_log('');

                if (cookie.get(Data.confirm) === 'perm') {
                    deleteDocumentTemplate(Data.documentId, function(success, data){
                        //		console_log(success);
                        //		console_log(data);

                        if ( success === 1 ) {
                            Site.removeItem(Data.documentsData.documentTemplates);
                        }
                    });
                } else {
                    Overlay.open.call(this, function(){
                        $('.message').text('Are you sure you want to delete this document?');
                    });
                }

                e.preventDefault();
            });
        },

        documentPrivileges: function(elem) {
            $(document).on('click', elem, function(e){
                Data.documentId = $(this).parents('.item').find('.title').attr('data-id');
                Data.documentIndex = $(this).parents('.item').index();

                Overlay.open.call(this, function(){
                    var organizations = Data.documentsData.documentTemplates[Data.documentIndex].linked_organizations || [];

                    Data.modals.documentsList(Data.organizationsData);
                    $.each(organizations, function(i, val){
                        $('.overlay .companies').find('input[value="' + val + '"]').attr('checked', true);
                    });
                    Plugins.initOverlay();
                });

                e.preventDefault();
            });
        },

        linkDocuments: function(elem) {
            $(document).on('click', elem, function(e){
                var organizationIds = [];

                $('.companies').find('input[type="checkbox"]:checked').each(function(){
                    organizationIds.push($(this).val());
                });

                linkDocumentToOrganizations(Data.documentId, organizationIds, function(success, data){
                    if ( success === 1 || (success === 0 && data.code === "1064") ) {
                        Data.documentsData.documentTemplates[Data.documentIndex].linked_organizations = organizationIds;
                        Overlay.closeTrigger();
                    } else {
                        if ($('.error-msg').length === 0) {
                            $('<div class="error-msg"></div>').text(data.message).prependTo(".form-actions");
                        } else {
                            $('.error-msg').text(data.message);
                        }
                    }
                });
            });
        },

        newClient: function(elem) {
            $(document).on('click', elem, function(e){
                Overlay.open.call(this, function(){
                    Data.templates.newClientCoaches(Data.coachesData);
                    Plugins.initOverlay();
                });

                e.preventDefault();
            });

            $(document).on('click', '.new-client-add', function(e){
                var values = {
                        coach_id: 			$('#new-client-coach').val(),
                        email: 				$('#new-client-email').val(),
                        first_name: 		$('#new-client-firstname').val(),
                        last_name: 			$('#new-client-lastname').val(),
                        focus_area: 		$('#new-client-focus').val(),
                        organization_id: 	window.location.search.substring(4), // TO DO for Organization Page
                        sessions_allotment: $('#new-client-sessions').val(),
                        sessions_frequency: $('#new-client-frequency').val(),
                        start_date: 		$('#new-client-date').val(),
                        success_metrics:	$('#new-client-success').val(),
                        organization_level: $('#new-client-level').val(),
                        tags:               $('#new-client-tags').val(),
                        sessions_frequency_other: $('#new-client-other').val(),
                        bill_rate:          $('#new-client-billrate').val()
                    },
                    validation = Data.validation([
                        '#new-client-email',
                        '#new-client-firstname',
                        '#new-client-lastname',
                        '#new-client-sessions',
                        '#new-client-focus',
                        '#new-client-frequency',
                        '#new-client-date',
                        '#new-client-success',
                        '#new-client-level',
                        '#new-client-billrate'
                    ]);

                var billValidation = new RegExp( '\\d+' );

                if (billValidation.test(values.bill_rate)) {
                    $('#new-client-billrate').removeClass('error');
                } else {
                    $('#new-client-billrate').addClass('error');
                }

                var sendEmail = $(this).attr('data-send') === 'true' ? true : false;

                if (validation && billValidation.test(values.bill_rate)) {
                    addClient(values, sendEmail, function(success, data){
                        //	console_log(success);
                        //	console_log(data);

                        if ( success === 1 ) {
                            values.id = data.id;
                            Data.clientsData.clients.push(values);
                            $('#clients-side').find('.styled-list').find('> li').not(':eq(0)').remove();
                            Data.templates.organizationClients(Data.clientsData);
                            Overlay.closeTrigger();
                        } else {
                            if ($('.error-msg').length === 0) {
                                $('<div class="error-msg"></div>').text(data.message).prependTo(".form-actions");
                            } else {
                                $('.error-msg').text(data.message);
                            }
                        }
                    });
                }

                e.preventDefault();
            });
        },

        editClient: function(elem) {
            $(document).on('click', elem, function(e){
                Data.clientId = $(this).parents('.item').find('.title').data('id');
                Data.clientItem = $(this).parents('.item');
                Data.clientIndex = $(this).parents('.item').index();

                var coachId = Data.clientsData.clients[Data.clientIndex].coach_id,
                    frequency = Data.clientsData.clients[Data.clientIndex].sessions_frequency;

                Overlay.open.call(this, function(){
                    Data.modals.editClient(Data.clientsData.clients[Data.clientIndex]);
                    Data.templates.newClientCoaches(Data.coachesData);

                    $('.overlay #new-client-frequency').find('option[value="' + frequency + '"]').attr('selected', true);
                    $('.overlay .new-client-coach').find('option[value="' + coachId + '"]').attr('selected', true);
                    Plugins.initOverlay();
                });

                e.preventDefault();
            });

            $(document).on('click', '.new-client-edit', function(e){
                var clientId = Data.clientId,
                    values = {
                        coach_id: 			$('#new-client-coach').val(),
                        email: 				$('#new-client-email').val(),
                        first_name: 		$('#new-client-firstname').val(),
                        last_name: 			$('#new-client-lastname').val(),
                        focus_area: 		$('#new-client-focus').val(),
                        organization_id: 	window.location.search.substring(4), // TODO for Organization Page
                        sessions_allotment: $('#new-client-sessions').val(),
                        sessions_frequency: $('#new-client-frequency').val(),
                        start_date: 		$('#new-client-date').val(),
                        success_metrics:	$('#new-client-success').val(),
                        organization_level: $('#new-client-level').val(),
                        tags:               $('#new-client-tags').val(),
                        sessions_frequency_other: $('#new-client-other').val(),
                        bill_rate:          $('#new-client-billrate').val()
                    },
                    validation = Data.validation([
                        '#new-client-email',
                        '#new-client-firstname',
                        '#new-client-lastname',
                        '#new-client-sessions',
                        '#new-client-focus',
                        '#new-client-frequency',
                        '#new-client-date',
                        '#new-client-success',
                        '#new-client-level',
                        '#new-client-billrate'
                    ]);

                console_log('');
                console_log('Sent data values:');
                console_log(values);
                console_log('');

                var billValidation = new RegExp( '\\d+' );

                if (billValidation.test(values.bill_rate)) {
                    $('#new-client-billrate').removeClass('error');
                } else {
                    $('#new-client-billrate').addClass('error');
                }

                var sendEmail = $(this).attr('data-send') === 'true' ? true : false;

                if (validation && billValidation.test(values.bill_rate)) {
                    updateClient(clientId, values, sendEmail, function(success, data){
                        //	console_log(success);
                        //	console_log(data);

                        if ( success === 1 ) {
                            $.each(Data.coachesData.coachs, function(i) {
                                if (this.id === values.coach_id) {
                                    values.coach_first_name = this.first_name;
                                    values.coach_last_name = this.last_name;
                                };
                            });
                            Site.valuesSwap(values, Data.clientsData.clients[Data.clientIndex]);
                            Data.clientItem.find('.title').text(values.first_name + ' ' + values.last_name);
                            Overlay.closeTrigger();
                        } else {
                            if ($('.error-msg').length === 0) {
                                $('<div class="error-msg"></div>').text(data.message).prependTo(".form-actions");
                            } else {
                                $('.error-msg').text(data.message);
                            }
                        }
                    });
                }

                e.preventDefault();
            });
        },

        deleteClient: function(elem) {
            $(document).on('click', elem, function(e){
                Data.item = $(this).parents('.item');
                Data.clientId = $(this).parents('.item').attr('data-id');
                Data.confirm = 'ctiClientDeletePermanent';

                console_log('');
                console_log('Client ID:');
                console_log(Data.clientId);
                console_log('');

                if (cookie.get(Data.confirm) === 'perm') {
                    deleteClient(Data.clientId, function(success, data){
                        console_log(success);
                        console_log(data);
                        
                        if ( success === 1 ) {
                            Site.removeItem(Data.clientsData.clients);
                        }
                    });
                } else {
                    Overlay.open.call(this, function(){
                        $('.message').text('Are you sure you want to delete this client?');
                    });
                }

                e.preventDefault();
            });
        },

        newCoach: function(elem) {
            $(document).on('click', elem, function(e){
                Overlay.open.call(this, function(){
                    Data.templates.coachOrganizations(Data.organizationsData);
                    Plugins.initOverlay();
                });

                e.preventDefault();
            });

            $(document).on('click', '.new-coach-add', function(){
                var values = {
                        first_name: $('#new-coach-firstname').val(),
                        last_name : $('#new-coach-lastname').val(),
                        email : $('#new-coach-email').val(),
                        bio: $('#new-coach-bio').val(),
                        expertise: $('#new-coach-credentials').val(),
                        schedule_url: $('#new-coach-timetrade').val(),
                        pay_rate: $('#new-coach-payrate').val()
                    },
                    validation = Data.validation([
                        '#new-coach-firstname',
                        '#new-coach-lastname',
                        '#new-coach-email'
                    ]),
                    organizationIds = [];

                $('.overlay .companies').find('input[type="checkbox"]:checked').each(function(){
                    organizationIds.push($(this).val());
                });

                console_log('');
                console_log('Sent data values:');
                console_log(values);
                console_log('');

                var sendEmail = $(this).attr('data-send') === 'true' ? true : false;

                if (validation) {
                    addCoach(values, sendEmail, function(success, data){                        
                        if ( success === 1 ) {
                            var coachId = data.id;

                            linkCoachToOrganizations(coachId, organizationIds, function(success, data){
                                if ( success === 1 ) {
                                    values.id = coachId;
                                    values.linked_organizations = organizationIds;
                                    Data.coachesData.coachs.push(values);
                                    $('ul.coaches').find('> li').not(':eq(0)').remove();
                                    Data.templates.adminCoaches(Data.coachesData);
                                    Overlay.closeTrigger();
                                } else {
                                    if ($('.error-msg').length === 0) {
                                        $('<div class="error-msg"></div>').text(data.message).prependTo(".form-actions");
                                    } else {
                                        $('.error-msg').text(data.message);
                                    }
                                }
                            });
                        } else {
                            if ($('.error-msg').length === 0) {
                                $('<div class="error-msg"></div>').text(data.message).prependTo(".form-actions");
                            } else {
                                $('.error-msg').text(data.message);
                            }
                        }
                    });
                }
            });
        },

        editCoach: function(elem, callOrg) {
            $(document).on('click', elem, function(e){
                Data.coachItem = $(this).parents('.item'),
                Data.coachIndex = $(this).parents('.item').index();
                Data.coachId = $(this).parents('.item').data('id');

                Overlay.open.call(this, function(){
                    Data.modals.coachProfile(Data.coachesData.coachs[Data.coachIndex]);
                    Data.templates.coachOrganizations(Data.organizationsData);

                    if (callOrg === true) {
                        getLinkedOrganizationForCoach(Data.coachId, function(success, data) {
                            var organizations = [];

                            $.each(data.organizations, function(i){
                                organizations.push(parseInt(data.organizations[i].id));
                            });

                            console_log(Data.coachIndex);
                            console_log(organizations);

                            $.each(organizations, function(i, val){
                                $('.overlay .companies').find('input[value="' + val + '"]').attr('checked', true);
                            });
                        });
                    } else {
                        var organizations = Data.coachesData.coachs[Data.coachIndex].linked_organizations || [];

                        console_log(Data.coachIndex);
                        console_log(organizations);

                        $.each(organizations, function(i, val){
                            $('.overlay .companies').find('input[value="' + val + '"]').attr('checked', true);
                        });
                    }
                });

                e.preventDefault();
            });

            $(document).on('click', '.new-coach-edit', function(e){
                var coachId = Data.coachesData.coachs[Data.coachIndex].id,
                    values = {
                        first_name: $('#new-coach-firstname').val(),
                        last_name : $('#new-coach-lastname').val(),
                        email : $('#new-coach-email').val(),
                        bio: $('#new-coach-bio').val(),
                        expertise: $('#new-coach-credentials').val(),
                        schedule_url: $('#new-coach-timetrade').val(),
                        pay_rate: $('#new-coach-payrate').val()
                    },
                    organizationIds = [];

                $('.overlay .companies').find('input[type="checkbox"]:checked').each(function(){
                    organizationIds.push($(this).val());
                });

                console_log('');
                console_log('xCoach ID:');
                console_log(coachId);
                console_log('Sent data values:');
                console_log(values);
                console_log('');

                var sendEmail = $(this).attr('data-send') === 'true' ? true : false;

                updateCoach(coachId, values, sendEmail, function(success, data){
                    console_log(success);
                    if (!isIE) console_log(data);

                    console_log('');
                    console_log('Linked ' + coachId + ' to ' + organizationIds);

                    linkCoachToOrganizations(coachId, organizationIds, function(success, data){
                        console_log(success);
                        console_log(data);

                        if ( success === 1 ) {
                            Site.valuesSwap(values, Data.coachesData.coachs[Data.coachIndex]);
                            Data.coachesData.coachs[Data.coachIndex].linked_organizations = organizationIds;
                            Data.coachItem.find('.title').text(values.first_name + ' ' + values.last_name);
                            Overlay.closeTrigger();
                        } else {
                            if ($('.error-msg').length === 0) {
                                $('<div class="error-msg"></div>').text(data.message).prependTo(".form-actions");
                            } else {
                                $('.error-msg').text(data.message);
                            }
                        }
                    });
                });

                e.preventDefault();
            });
        },

        deleteCoach: function(elem) {
            $(document).on('click', elem, function(e){
                Data.item = $(this).parents('.item');
                Data.coachId = $(this).parents('.item').attr('data-id');
                Data.confirm = 'ctiCoachDeletePermanent';

                console_log('');
                console_log('Coach ID:');
                console_log(Data.coachId);
                console_log('');

                if (cookie.get(Data.confirm) === 'perm') {
                    deleteCoach(Data.coachId, function(success, data){
                        console_log(success);
                        console_log(data);

                        if ( success === 1 ) {
                            Site.removeItem(Data.coachesData.coachs);
                        }
                    });
                } else {
                    Overlay.open.call(this, function(){
                        $('.message').text('Are you sure you want to delete this coach?');
                    });
                }

                e.preventDefault();
            });
        },

        editCoachProfile: function(elem) {
            $(document).on('click', elem, function(e){
                Overlay.open.call(this, function(){
                    Data.modals.editCoachProfile(Data.coachData.coachs[0]);
                    Data.modals.editCoachProfileList(Data.clientsData);
                    Table.fixedHeader('#modal-coach-profile');

                    $(document).on('click', '#coach-profile-add', function(e){
                        console_log('foo');
                        var coachId = Data.coachData.coachs[0].id,
                            values = {
                                first_name : $('#coach-profile-firstname').val(),
                                last_name : $('#coach-profile-lastname').val(),
                                bio : $('#coach-profile-bio').val(),
                                expertise : $('#coach-profile-credentials').val(),
                                schedule_url : $('#coach-profile-timetrade').val(),
                                phone: $("#coach-profile-phone").val()
                            },
                            validation = Data.validation([
                                '#coach-profile-firstname',
                                '#coach-profile-lastname',
                                '#coach-profile-timetrade'
                            ]),
                            urlValidation = new RegExp( '^(http|https|ftp)\://[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(:[a-zA-Z0-9]*)?/?([a-zA-Z0-9\-\._\?\,\'/\\\+&amp;%\$#\=~])*[^\.\,\)\(\s]$' );

                        if (urlValidation.test(values.schedule_url)) {
                            $('#coach-profile-timetrade').removeClass('error');
                        } else {
                            $('#coach-profile-timetrade').addClass('error');
                        }

                        console_log('');
                        console_log('Coach ID:');
                        console_log(coachId);
                        console_log('Sent data values:');
                        console_log(values);
                        console_log('');

                        if (validation && urlValidation.test(values.schedule_url)) {
                            updateCoach(coachId, values, function(success, data){
                                console_log(success);
                                console_log(data);

                                if ( success === 1 ) {
                                    location.reload();
                                } else {
                                    if ($('.error-msg').length === 0) {
                                        $('<div class="error-msg"></div>').text(data.message).prependTo(".form-actions");
                                    } else {
                                        $('.error-msg').text(data.message);
                                    }
                                }
                            });
                        };

                        e.preventDefault();
                    });
                });

                e.preventDefault();
            });
        },

        viewOrganization: function(elem) {
            $(document).on('click', elem, function(e){
                var id = $(this).parents('.item').attr('data-id');

                window.location = "account-manager-organization.html?id="+id;
                e.preventDefault();
            });
        },

        newOrganization: function(elem) {
            var values = {},
                uploader;

            $(document).on('click', elem, function(e){
                Overlay.open.call(this, function(){
                    uploader = new qq.FileUploader({
                        element: $('#logo-upload')[0],
                        uploadButtonText: 'choose',
                        sizeLimit: 8*1024*1024,
                        action: SERVER_URI + '/logo-upload',
                        autoUpload: false,
                        multiple: false,
                        onComplete: function(id, fileName, responseJSON) {
                            console_log('completed');
                            console_log(JSON.stringify(responseJSON));

                            if (responseJSON.success === "false") alert(responseJSON.message)
                            

                            Data.organizationsData.organizations.push(values);
                            $('ul.organizations').find('> li').not(':eq(0)').remove();
                            Data.templates.adminOrganizations(Data.organizationsData);
                            Overlay.closeTrigger();
                        }
                    });

                    $('.qq-upload-list').append('<li class="placeholder">Select logo</li>');

                    Plugins.initOverlay();
                });

                e.preventDefault();
            });

            $(document).on('click', '.new-organization-add', function(){
                values = {
                    first_name          : $('#new-organization-firstname').val(),
                    last_name           : $('#new-organization-lastname').val(),
                    email               : $('#new-organization-email').val(),
                    organization_name   : $('#new-organization-name').val(),
                    addr_street         : $('#new-organization-street').val(),
                    addr_city           : $('#new-organization-city').val(),
                    addr_state          : $('#new-organization-state').val(),
                    addr_zip            : $('#new-organization-zip').val(),
                    notes               : $('#new-organization-info').val(),
                    budget              : $('#new-organization-budget').val().replace(/\./g, '').replace(/\,/g, ''),
                    phone               : $('#new-organization-phone').val()
                };

                var validation = Data.validation([
                        '#new-organization-name',
                        '#new-organization-budget',
                        '#new-organization-firstname',
                        '#new-organization-lastname',
                        '#new-organization-email'
                    ]),
                    phoneValidation = new RegExp( '[0-9-\s+]*' ),
                    budgetValidation = new RegExp( '[0-9\s,\.]+' );

                if (phoneValidation.test(values.phone)) {
                    $('#new-organization-phone').removeClass('error');
                } else {
                    $('#new-organization-phone').addClass('error');
                }

                if (budgetValidation.test(values.budget)) {
                    $('#new-organization-budget').removeClass('error');
                } else {
                    $('#new-organization-budget').addClass('error');
                }

                console_log('');
                console_log('Sent data values:');
                console_log(values);
                console_log('');

                var sendEmail = $(this).attr('data-send') === 'true' ? true : false;

                if (validation && phoneValidation.test(values.phone) && budgetValidation.test(values.budget)) {
                    addOrganization(values, sendEmail, function(success, data){
                        console_log(success);
                        console_log(data);

                        if ( success === 1 ) {
                            if ($('.qq-upload-file').length !== 0) {
                                console_log('upload triggered');

                                $('#new-organization-add').val('Uploading...');
                                values.id = data.id;
                                uploader.setParams({
                                    organizationId: values.id
                                });
                                uploader.uploadStoredFiles();
                            } else {
                                values.id = data.id;
                                Data.organizationsData.organizations.push(values);
                                $('ul.organizations').find('> li').not(':eq(0)').remove();
                                Data.templates.adminOrganizations(Data.organizationsData);
                                Overlay.closeTrigger();
                            }
                        } else {
                            if ($('.error-msg').length === 0) {
                                $('<div class="error-msg"></div>').text(data.message).prependTo(".form-actions");
                            } else {
                                $('.error-msg').text(data.message);
                            }
                        }
                    });
                }
            });
        },

        editOrganization: function(elem) {
            var item,
                i,
                organizationId,
                uploader,
                values = {};

            $(document).on('click', elem, function(e){
                item = $(this).parents('.item'),
                i = item.index(),
                organizationId = $(this).parents('.item').attr('data-id');

                Overlay.open.call(this, function(){
                    Data.modals.organizationProfile(Data.organizationsData.organizations[i]);

                    var state = 'option[value="' + $('#new-organization-state').attr('data-state') + '"]';

                    $('#modal-new-organization').find(state).attr("selected", true);

                    uploader = new qq.FileUploader({
                        element: $('#logo-upload')[0],
                        uploadButtonText: 'choose',
                        sizeLimit: 8*1024*1024,
                        action: SERVER_URI + '/logo-upload',
                        autoUpload: false,
                        multiple: false,
                        onComplete: function(id, fileName, responseJSON) {
                            console_log('completed');
                            console_log(JSON.stringify(responseJSON));

                            if (responseJSON.success === "false") alert(responseJSON.message)
                            
                            Site.valuesSwap(values, Data.organizationsData.organizations[i]);
                            item.find('.title').text(values.organization_name);
                            Overlay.closeTrigger();
                        }
                    });

                    $('.qq-upload-list').append('<li class="placeholder">Select logo</li>');
                    Plugins.initOverlay();
                    $('.dk_container').addClass('dk_theme_default');
                });

                e.preventDefault();
            });

            $(document).on('click', '.new-organization-edit', function(e){
                values = {
                    first_name          : $('#new-organization-firstname').val(),
                    last_name           : $('#new-organization-lastname').val(),
                    email               : $('#new-organization-email').val(),
                    organization_name   : $('#new-organization-name').val(),
                    addr_street         : $('#new-organization-street').val(),
                    addr_city           : $('#new-organization-city').val(),
                    addr_state          : $('#new-organization-state').val(),
                    addr_zip            : $('#new-organization-zip').val(),
                    notes               : $('#new-organization-info').val(),
                    budget              : $('#new-organization-budget').val().replace(/\./g, '').replace(/\,/g, ''),
                    phone               : $('#new-organization-phone').val()
                };

                var validation = Data.validation([
                        '#new-organization-name',
                        '#new-organization-budget',
                        '#new-organization-firstname',
                        '#new-organization-lastname',
                        '#new-organization-email'
                    ]),
                    phoneValidation = new RegExp( '[0-9-\s+]*' ),
                    budgetValidation = new RegExp( '[0-9\s,\.]+' );

                if (phoneValidation.test(values.phone)) {
                    $('#new-organization-phone').removeClass('error');
                } else {
                    $('#new-organization-phone').addClass('error');
                }

                if (budgetValidation.test(values.budget)) {
                    $('#new-organization-budget').removeClass('error');
                } else {
                    $('#new-organization-budget').addClass('error');
                }

                console_log('');
                console_log('Organization ID:');
                console_log(organizationId);
                console_log('Sent data values:');
                console_log(values);
                console_log('');

                var sendEmail = $(this).attr('data-send') === 'true' ? true : false;

                if (validation && phoneValidation.test(values.phone) && budgetValidation.test(values.budget)) {
                    updateOrganization(organizationId, values, sendEmail, function(success, data){
                        console_log(success);
                        console_log(data);

                        if ( success === 1 ) {
                            if ($('.qq-upload-file').length !== 0) {
                                console_log('upload triggered');

                                $('#new-organization-add').val('Uploading...');
                                uploader.setParams({
                                    organizationId: organizationId
                                });
                                uploader.uploadStoredFiles();
                            } else {
                                Site.valuesSwap(values, Data.organizationsData.organizations[i]);
                                item.find('.title').text(values.organization_name);
                                Overlay.closeTrigger();
                            }                            
                        } else {
                            if ($('.error-msg').length === 0) {
                                $('<div class="error-msg"></div>').text(data.message).prependTo(".form-actions");
                            } else {
                                $('.error-msg').text(data.message);
                            }
                        }
                    });
                }
            });
        },

        deleteOrganization: function(elem) {
            $(document).on('click', elem, function(e){
                Data.item = $(this).parents('.item');
                Data.organizationId = $(this).parents('.item').attr('data-id');
                Data.confirm = 'ctiOrganizationDeletePermanent';

                console_log('');
                console_log('Organization ID:');
                console_log(Data.organizationId);
                console_log('');

                if (cookie.get(Data.confirm) === 'perm') {
                    deleteOrganization(Data.organizationId, function(success, data){
                        console_log(success);
                        console_log(data);

                        if ( success === 1 ) {
                            Site.removeItem(Data.organizationsData.organizations);
                        }
                    });
                } else {
                    Overlay.open.call(this, function(){
                        $('.message').text('Are you sure you want to delete this organization?');
                    });
                }

                e.preventDefault();
            });
        },

        newDocument: function(elem) {
            $(document).on('click', elem, function(e){
                Overlay.open.call(this, function(){
                    Data.templates.documentOrganizations(Data.organizationsData);

                    var uploader = new qq.FileUploader({
                        element: $('#new-document-upload')[0],
                        uploadButtonText: 'choose',
                        sizeLimit: 8*1024*1024,
                        action: SERVER_URI + '/upload',
                        autoUpload: false,
                        multiple: false,
                        onComplete: function(id, fileName, responseJSON) {
                            console_log(JSON.stringify(responseJSON));

                            if (responseJSON.success === "true") {
                                var organizationIds = [];

                                var values = {
                                    confidential: $('input[name="confidential"]:checked').val(),
                                    id: responseJSON.id,
                                    linked_organizations: organizationIds,
                                    readonly: $('#new-document-readonly').is(':checked') ? '1' : '0',
                                    title: $('#new-document-title').val() === 'Title' || $('#new-document-title').val() === '' ? $('.qq-upload-file').text() : $('#new-document-title').val(),
                                    url: SERVER_URI + '/uploads/' + $('.qq-upload-file').text() //TODO Dynamic url || url in variable
                                };

                                $('.overlay .companies').find('input[type="checkbox"]:checked').each(function(){
                                    organizationIds.push($(this).val());
                                });

                                if(organizationIds[0] !== undefined) {
                                    linkDocumentToOrganizations(responseJSON.id, organizationIds, function(success, data){
                                        console_log(success);
                                        console_log(data);

                                        if ( success === 1 ) {
                                            Data.documentsData.documentTemplates.push(values);
                                            $('ul.documents').find('> li').not(':eq(0)').remove();
                                            Data.templates.adminDocuments(Data.documentsData);
                                            Overlay.closeTrigger();
                                        } else {
                                            if ($('.error-msg').length === 0) {
                                                $('<div class="error-msg"></div>').text(data.message).prependTo(".form-actions");
                                            } else {
                                                $('.error-msg').text(data.message);
                                            }
                                        }
                                    });
                                } else {
                                    Data.documentsData.documentTemplates.push(values);
                                    $('ul.documents').find('> li').not(':eq(0)').remove();
                                    Data.templates.adminDocuments(Data.documentsData);
                                    Overlay.closeTrigger();
                                }
                            } else {
                                //TODO error message
                                location.reload();
                            }
                        }
                    });

                    $(document).on('click', '#new-document-add', function(){
                        if ($('.qq-upload-file').text() !== '') {
                            $(this).val('Uploading...');
                        } else {
                            if ($('.error-msg').length === 0) {
                                $('<div class="error-msg"></div>').text("Please choose file.").prependTo(".form-actions");
                            }
                            return false;
                        };

                        uploader.setParams({
                            title: function(){
                                if ($('#new-document-title').val() === 'Title' || $('#new-document-title').val() === '') {
                                    return $('.qq-upload-file').text();
                                } else {
                                    return $('#new-document-title').val();
                                }
                            },
                            confidential: $('#modal-new-document input[name="confidential"]:checked').val(),
                            readonly: function(){
                                if ($('#new-document-readonly').is(':checked')) {
                                    return '1'
                                } else {
                                    return '0'
                                }
                            }
                        });

                        uploader.uploadStoredFiles();
                    });

                    Plugins.initOverlay();
                });

                e.preventDefault();
            });
        },

        confirmation: function() {
            $(document).on('click', '#confirm-confirm', function(){
                if ($('#confirm-permanent').is(':checked')) {
                    cookie.set(Data.confirm, 'perm', {
                        expires: 365
                    });
                }

                switch (Data.confirm) {
                    case 'ctiSessionEditPermanent':
                        updateSession(Data.sessionId, Data.callValues, function(success, data){
                            console_log(success);
                            console_log(data);

                            if ( success === 1 ) { 
                                // TODO
                                location.reload();
                            }
                        });
                        break;

                    case 'ctiCoachDeletePermanent':
                        deleteCoach(Data.coachId, function(success, data){
                            console_log(success);
                            console_log(data);

                            if ( success === 1 ) {
                                Site.removeItem(Data.coachesData.coachs);
                            }
                        });
                        break;

                    case 'ctiClientDeletePermanent':
                        deleteClient(Data.clientId, function(success, data){
                            console_log(success);
                            console_log(data);

                            if ( success === 1 ) {
                                Site.removeItem(Data.clientsData.clients);
                            }
                        });
                        break;

                    case 'ctiOrganizationDeletePermanent':
                        deleteOrganization(Data.organizationId, function(success, data){
                            console_log(success);
                            console_log(data);

                            if ( success === 1 ) {
                                Site.removeItem(Data.organizationsData.organizations);
                            }
                        });
                        break;

                    case 'ctiDocumentDeletePermanent':
                        deleteDocumentTemplate(Data.documentId, function(success, data){
                            console_log(success);
                            console_log(data);

                            if ( success === 1 ) { 
                                Site.removeItem(Data.documentsData.documentTemplates);
                            }
                        });
                        break;

                    case 'ctiSessionDeletePermanent':
                        deleteSession(Data.sessionId, function(success, data){
                            console_log(success);
                            console_log(data);

                            if ( success === 1 ) { 
                                Site.removeItem(Data.sessionsData.sessions);
                                $('.sessions').find('.body').find('tr').removeClass('ui-state-active').filter(':odd').addClass('ui-state-active');
                            }
                        });
                        break;

                    case 'ctiOrganizationSessionDeletePermanent':
                        deleteSession(Data.sessionId, function(success, data){
                            console_log(success);
                            console_log(data);

                            if ( success === 1 ) { 
                                getSessionsForOrganization(Data.orgId, function(success, data) {
                                    $('.sessions').find('.body').find('tr').not(':eq(0)').remove();

                                    Data.sessionsData = data;
                                    Data.templates.organizationSession(data);

                                    $('tbody tr').each(function(){
                                        var i = $(this).index();
                                        $(this).find('.client, .coach').attr('data-index', i);
                                    });

                                    $('.sessions .body').find('tr:odd').addClass('ui-state-active');

                                    $('#all-count').find('span').text($('#all').find('tbody tr').length);
                                    $('#done-count').find('span').text($('#done').find('tbody tr').length);
                                    $('#noshows-count').find('span').text($('#noshows').find('tbody tr').length);
                                    $('#canceled-count').find('span').text($('#canceled').find('tbody tr').length);

                                    Overlay.closeTrigger();
                                });
                            }
                        });
                        break;
                };
            });
        }
    }
};

(function($) {
    Site.init();

    Plugins.init();

    Site.tabs('.tabs li');
    Site.tags();
    Data.overlays.editTimezone('.user-timezone');

    // Turn this on on deployment
    // Site.newTabPrevent();

    Overlay.openLightbox();

    if ($('body.login').length) {
        Login.validation();
        Login.forgot();
    };

    if (!$('body.login').length) {
        Data.init();
        Login.logout();
    };
})(jQuery);

// 0 poorni@coactive.com - Account Administrator
// 1 js34223@mailinator.com - Org Sponsor
// 2 fangcoach4@mailinator.com - Coach
// 3 jeremytest19@mailinator.com - Client
// 4 ellen@thecoaches.com - Accounting
// 5 invalid@thecoaches.com - Invalid


function formatMilitary(date) {
    var hours = date.getHours();
    var minutes = date.getMinutes();
    minutes = minutes < 10 ? '0'+minutes : minutes;
    strTime = hours + ':' + minutes + ':00';
    var month =  date.getMonth()+1;
    var day = date.getDate();
    var year = date.getFullYear();
    month = month < 10 ? '0'+month : month;
    day = day < 10 ? '0'+day : day;
    return month + "-" + day + "-" + year + " " + strTime;
}
