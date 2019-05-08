<div id="panel" class="profile">
    <h3>
        {{ page_title }}
    </h3>

    <p class="intro">
        Change your account information, avatar, login credentials, etc.
    </p>

    <form method="post">
        <div class="form-group avatar-field clearfix">
            <div class="col-sm-3">
                <img src="{{ user['imageUrl']}}" class="img-responsive img-circle" id="avatarImg" />
            </div>
            <div class="col-sm-9">
                <label>Set up your avatar picture</label>
                <input type="file" name="avatar"/>
            </div>
        </div>
        <div class="form-group">
            <label>First Name</label>
            <input type="text" class="form-control" name="firstName" placeholder="Enter First Name" value="{{ user['firstName'] }}" />
        </div>
        <div class="form-group">
            <label>Last Name</label>
            <input type="text" class="form-control" name="lastName" placeholder="Enter Fast Name" value="{{ user['lastName'] }}" />
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="text" class="form-control"  value="{{ user['email'] }}" readonly/>
        </div>
        <div class="form-group">
            <label>Website URL</label>
            <input type="text" class="form-control" name="websiteUrl" placeholder="Enter Website URL" value="{{ user['websiteUrl'] }}" />
        </div>
        <div class="form-group">
            <label>Company Name</label>
            <input type="text" class="form-control" name="companyName" placeholder="Enter Company Name" value="{{ user['companyName'] }}" />
        </div>
        <div class="form-group">
            <label>Company City</label>
            <input type="text" class="form-control" name="companyCity" placeholder="Enter Company City" value="{{ user['companyCity'] }}" />
        </div>
        <div class="form-group">
            <label>Company Country</label>
            <input type="text" class="form-control" name="companyCountry" placeholder="Enter Company Country" value="{{ user['companyCountry'] }}" />
        </div>

        <div class="form-group action">
            <button class="btn btn-primary" id="save">Save changes</button>
        </div>
    </form>
</div>
{% include "settings/_settingsJs.volt" %}
