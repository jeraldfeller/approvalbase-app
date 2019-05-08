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
</div>
{% include "settings/_supportJs.volt" %}