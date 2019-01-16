<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Admin</title>
        <script src="packages/qinoa/apps/ng-admin/ng-admin.min.js" type="text/javascript"></script>
        <script src="packages/qinoa/apps/admin.js" type="text/javascript"></script>

        <link rel="stylesheet" href="packages/qinoa/apps/ng-admin/ng-admin.min.css">
        <link rel="stylesheet" href="packages/qinoa/assets/css/bootstrap.css">

        <style>
        .btn-toolbar>.btn-group {
            margin-bottom: 5px;
        }

        #toolbarWC, #toolbarCC {
            height: 28.8px !important;
            padding-top: 5px;
            padding-bottom: 5px;
        }        
        
        .loader {
          display: block;
          position: absolute;
          width: 300px;
          height: 100px;
          top: calc(50% - 50px);
          left: calc(50% - 150px);
          text-align: center;
        }
        
        .lds-roller {
          display: block;
          position: relative;
          width: 64px;
          height: 64px;
          padding-left: calc(50% - 32px);
        }
        .lds-roller div {
          animation: lds-roller 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
          transform-origin: 32px 32px;
        }
        .lds-roller div:after {
          content: " ";
          display: block;
          position: absolute;
          width: 6px;
          height: 6px;
          border-radius: 50%;
          background: #000;
          margin: -3px 0 0 -3px;
        }
        .lds-roller div:nth-child(1) {
          animation-delay: -0.036s;
        }
        .lds-roller div:nth-child(1):after {
          top: 50px;
          left: 50px;
        }
        .lds-roller div:nth-child(2) {
          animation-delay: -0.072s;
        }
        .lds-roller div:nth-child(2):after {
          top: 54px;
          left: 45px;
        }
        .lds-roller div:nth-child(3) {
          animation-delay: -0.108s;
        }
        .lds-roller div:nth-child(3):after {
          top: 57px;
          left: 39px;
        }
        .lds-roller div:nth-child(4) {
          animation-delay: -0.144s;
        }
        .lds-roller div:nth-child(4):after {
          top: 58px;
          left: 32px;
        }
        .lds-roller div:nth-child(5) {
          animation-delay: -0.18s;
        }
        .lds-roller div:nth-child(5):after {
          top: 57px;
          left: 25px;
        }
        .lds-roller div:nth-child(6) {
          animation-delay: -0.216s;
        }
        .lds-roller div:nth-child(6):after {
          top: 54px;
          left: 19px;
        }
        .lds-roller div:nth-child(7) {
          animation-delay: -0.252s;
        }
        .lds-roller div:nth-child(7):after {
          top: 50px;
          left: 14px;
        }
        .lds-roller div:nth-child(8) {
          animation-delay: -0.288s;
        }
        .lds-roller div:nth-child(8):after {
          top: 45px;
          left: 10px;
        }
        @keyframes lds-roller {
          0% {
            transform: rotate(0deg);
          }
          100% {
            transform: rotate(360deg);
          }
        }
        
        ma-boolean-column > span[class~="glyphicon-ok"] {
            color: #25978b;
        }        
        ma-boolean-column > span[class~="glyphicon-remove"] {
            color: #923e00;        
        }
        .text-success {
            color: #25978b;
        }
        .text-danger {
            color: #923e00;
        }
        
        </style>        
    </head>
    <body>
   
        <div class="loader" ng-if="!isReady">
            <div id="loader">
                <div class="lds-roller" >
                    <!-- bullets -->
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>               
                </div>
                <div>
                    Loading server configuration...
                </div>
            </div>
        </div>
        
        <div ui-view="ng-admin"></div>

    </body>
</html>
