<!DOCTYPE html>

<html lang="fr">
<head>

<meta charset="UTF-8" />

<title>Qinoa</title>
<head>
<link rel="stylesheet" href="packages/core/html/css/jquery-ui.min.css" type="text/css" />
<link rel="stylesheet" href="packages/core/html/css/qinoa-ui.min.css" type="text/css" />
<link rel="stylesheet" href="packages/core/html/css/font-awesome.min.css" type="text/css" />



<script language="javascript" type="text/javascript" src="packages/core/html/js/jquery.min.js"></script>
<script language="javascript" type="text/javascript" src="packages/core/html/js/jquery-ui.min.js"></script>
<script language="javascript" type="text/javascript" src="packages/core/html/js/qinoa.api.min.js"></script>
<script language="javascript" type="text/javascript" src="packages/core/html/js/qinoa-ui.min.js"></script>



</head>
<style>
html, body {
    border: 0;
    padding: 0;
 	margin: 0;
    height: 100%;
}


#qn-head {
    position: relative;
    background-color: #870101;
    width: 100%;
    height: 18pt;
}

#qn-head .qn-access-pane {
    position: absolute;
    top: 0;
    left: 5pt;
    cursor: pointer;
    color: white;
    font-size: 12pt;    
}

#qn-head .qn-access-login {
    position: absolute;
    top: 0;
    right: 10pt;
    cursor: pointer;
    font-size: 10pt;  
    margin-top: 3pt;
    color: white !important;
}

#qn-head .qn-access-login a {
    text-decoration: none;
    color: white;
}

#qn-head .qn-access-login a:hover {
    text-decoration: underline;
}

#qn-body {
    position: absolute;
    display: block;
    margin: 0;
    padding: 0;
    border: 0;
    z-index: 0;
    width: 100%;
    height: calc(100% - 18pt);
    overflow: hidden;
}

#qn-pane {
    display: block;
    position: absolute;
    z-index: -200;
    width: 150pt;
    height: 100%;
    left: 0;
    top: 0;
    box-sizing: border-box;
}

#qn-pane .qn-menu {
    position: absolute;
    display: block !important;
    left: 0;
    top: 10pt;
    height: 100%;
    width: 150pt !important;
    border-radius: 0;
    box-sizing: border-box;
}
#qn-pane #qn-menu {
    left: 0pt;
}
#qn-pane #qn-sub-menu {
    left: 150pt;
}

#qn-pane .qn-menu ul {
    border: 0;
    margin: 0;
    padding: 0;
    height: 100% !important;
    width: 100% !important;
}

#qn-pane-header {
    position: absolute;
    height: 10pt;
    width: 150pt;
}

#qn-content {
    position: absolute;
    border: 0;
    margin: 0;
    padding: 0;
    left: 0;
    top: 0;
    z-index: -1;
    height: 100% !important;
    width: 100% !important;
    background: #f4f2f4;
    background-size: 50%;
}

#qn-content::before {
    position: absolute;
    font-family: "Qinoa";
    font-size: 200pt;
    color: white;
    text-shadow: 2px 5px 2px rgba(0,0,0,0.5);
    padding-top: 10%;
    width: 100%;
    text-align: center;
    content: "Qinoa";
    z-index -1;
}

#main {
    position: absolute;
    z-index: 100;
    width: 100%;
    height: 100%;
    overflow: scroll;
}

.qn-pane-nav-item {
    position: absolute;
    cursor: pointer;
    font-size: 10pt;
    line-height: 10pt;
}

.qn-pane-nav-item a {
    text-decoration: none !important;
    background: none !important;
    border: 0 !important;
}


</style>
<script>

$(document).ready(function() {
 

    qinoa.loader.show($('body'));
    
    // handle clicks on access pane
    $('#qn-head > .qn-access-pane').on('click', function() {
        var pos = parseInt($('#qn-content').css('left'));
        if(pos) {
            // close pannel
            $('#qn-content').animate({'left': '0'}, 'fast', 'linear', function() {
                // reset panel margin
                $('#qn-pane').css({'marginLeft': '0'});
                // unselect all items
                $('#qn-menu li').removeClass('ui-state-selected ui-state-active');
            });        
        }
        else {
            // open panel
            $('#qn-content').animate({'left': '150pt'}, 'fast');  
        }
    });    
       
    
	$.when(qinoa.get_view('core\\Dashboard', 'menu.default'))
	.fail(function (code)	{ console.log('view load failed: ' + code); })    
	.done(function (view)	{
        var $menu = $('<ul />');
        
        // handle clicks on main menu items
        $.each($(view).children(), function(i, item) {
            var $item = $(item);
       
            $('<li />').text($item.attr('name'))
            .on('click', function() {
                var $submenu = $(item.innerHTML)

                $submenu.find('li').on('click', function() {
                        var $this = $(this);
                        
                        var config =  {
                            class_name: $this.attr('target'),
                            view: 'list.default',                            
                            sortname: $this.attr('sortname') || 'id',
                            sortorder: $this.attr('sortorder') || 'asc',
                            domain: eval($this.attr('domain')) || [[]],
                            url: ($('#recycle')[0].checked)?'index.php?get=core_objects_list&mode=recycle':'',
                            permanent_deletion: ($('#recycle')[0].checked)?true:false
                        };
                        
                        qinoa.loader.show($('#main').empty());
                            
                        $('#main')
                        .append($('<div/>').qSearchGrid(config).on('ready', function() {
                            qinoa.loader.hide();
                        }));
                        
                        
                        $('#qn-head > .qn-access-pane').trigger('click');
                });

                // destroy old sub-menu
                if($('#qn-sub-menu').hasClass('ui-panel')) {
                   $('#qn-sub-menu').panel('destroy').empty();
                }
                // create new sub-menu
                $('#qn-sub-menu').append($submenu);
                $('#qn-sub-menu').panel({
                    width: '150pt',
                    linkWidth: 200
                });
                // slide pane towards left
                $('#qn-pane').animate({'marginLeft': '-150pt'}, 'fast'); 
            })            
            .appendTo($menu); 
        });

 
        // create main menu
        $('#qn-menu').append($menu)
        .panel({
            width: '150pt',
            linkWidth: 200
        });
        
        // add menu navigation items
        $('#qn-pane')
        .append(
            $('<div />').addClass('qn-pane-nav-item').css({'left': '1pt'})
            .append( $('<a />').addClass('fa fa-angle-double-left ui-widget ui-state-disabled') )
        )
        .append(
            $('<div />').addClass('qn-pane-nav-item').css({'right': '1pt'})
            .append( $('<a />').addClass('fa fa-angle-double-right ui-widget ui-state-disabled') )
        )          
        .append(
            $('<div />')
            .addClass('qn-pane-nav-item')
            .css({'left': '151pt'})
            .append(
                $('<a />')
                .hover(function() { $(this).toggleClass('ui-state-selected ui-state-active'); })
                .addClass('fa fa-angle-double-left ui-widget ui-state-default')
                .on('click', function() {
                    $('#qn-pane').animate({'marginLeft': '0pt'}, 'fast');     
                })
            )
        );
        
        qinoa.loader.hide();  
    });
});
</script>

</head>

<html>

<body>

    <div id="qn-head">
        <div class="qn-access-pane">
            <span style="font-family:'Qinoa';">Q</span>&nbsp;&nbsp;&nbsp;<i class="fa fa-navicon" aria-hidden="true"></i>
        </div>    
            
        <div class="qn-access-login">
            <a href="/index.php?do=core_user_logout">Log out</a>
        </div>
    </div>

    <div id="qn-body">
        
        <div id="qn-pane">
            <div id="qn-menu" class="qn-menu"></div>
            <div id="qn-sub-menu" class="qn-menu"></div>
            
            <input type="checkbox" id="recycle" style="display: none;"/>
        </div>
        
        <div id="qn-content">
            <div id="main"></div>
        </div>
    </div>

</body>

</html>
