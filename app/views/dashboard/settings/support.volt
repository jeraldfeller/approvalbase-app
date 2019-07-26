<div id="panel" class="profile">
    <h3>
        {{ page_title }}
    </h3>

    <p class="intro">
    </p>

    <form method="post">
        <div class="form-group">
            <label>Subject</label>
            <input type="text" class="form-control" name="subject" id="subject" placeholder="Enter Subject" value="" />
        </div>
        <div class="form-group">
            <label>Message</label>
            <textarea rows="7" class="form-control" name="message" id="message"></textarea>
        </div>
        <div class="form-group action">
            <button class="btn btn-primary" id="send">Send</button>
        </div>
    </form>

    <hr>
    <h3>
        Restart Onboarding
    </h3>
    <p class="intro">
    </p>
    <button class="btn btn-primary" data-toggle="modal" data-target="#confirmModal">Restart</button>


    <div class="modal fade" id="confirmModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="modalTitle">
                        <i class="fa fa-info-circle"></i> Restart Onboarding
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <p>Are you sure do you want to restart onboarding?</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="row">
                        <div class="col-md-12">
                            <button class="btn btn-primary pull-right" id="restartOnboardingBtn">Restart</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
{% include "settings/_supportJs.volt" %}