<?php
/* @var $this \P3rc1val\templates\TemplateParser*/
    $this->addCssFile('assets/css/MainPanel');
    $this->addCssFile('assets/css/darkener');
?>

<div class="modal" id="wsServerPanel" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Serwer websocket</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row" id="wsPanel_loading">
                    <i class="fas fa-circle-notch fa-spin mx-auto font-2"></i>
                </div>
                <div id="wsPanel_content"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="hostFileBrowser" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="HFB_title">-</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row" id="HFB_loading">
                    <i class="fas fa-circle-notch fa-spin mx-auto font-2"></i>
                </div>
                <div id="HFB_content"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <a href="logout">Wyloguj <?=$this->user->login?></a>
    </div>
    <div class="row">
        <div class="alert">
            <div class="form-group">
                <span class="control-label">Websocket connection status</span>
            </div>
            <div class="btn-group">
                <button class="btn btn-secondary" id="wsServerToggle">
                    <span id="wsServerStatus">DISCONNECTED</span>
                    <i class="fas fa-circle-notch fa-spin d-none" id="serverLoading"></i>
                </button>
                <button class="btn btn-primary" id="autoReconnect">
                    <i class="fas fa-check"></i>
                </button>
            </div>
            <button class="btn btn-secondary" id="wsServerPanelButton">Panel WS</button>
            <button class="btn btn-secondary" id="downloadNewestJarButton">
                <i class="fas fa-download"></i>
            </button>
        </div>
    </div>

    <div class="row">

        <div class="col-sm-4">
            <div class="card">
                <div class="card-header bg-primary">
                    <h3 class="panel-title">Lista hostów</h3>
                </div>
                <div class="card-body" id="host_list">

                </div>
            </div>
        </div>
        <div class="col-sm-8">
            <div class="card">
                <div class="card-heading bg-primary">
                    <div class="btn-group">
                        <button disabled class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-circle-notch fa-spin d-none" id="hostConnectionLoading"></i>
                            <span id="host_name">Nie wybrano</span>
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="#" id="disconnect_host">Rozłącz</a>
                        </div>
                    </div>
                    <button id="hostNotResponding" class="d-none btn progress-bar-striped progress-bar-animated bg-warning">
                        Oczekiwanie na powrót hosta
                    </button>
                </div>
                <div class="card-body" id="host_control_panel"></div>
            </div>
        </div>
    </div>

</div>