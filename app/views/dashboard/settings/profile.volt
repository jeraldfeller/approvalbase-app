<div id="panel" class="profile">
    <h3>
        {{ page_title }}
    </h3>

    <p class="intro">
        Change your account information, avatar, login credentials, etc.
    </p>

    <form method="post">

        <div class="form-group avatar-field clearfix">
            <div class="col-sm-3 col-xs-3 avatar-round profile-photo" style="background-image: url('{{ user['imageUrl']}}?v={{ unix }}')">
            </div>
            <div class="col-sm-9 col-xs-9">
                <label>Set up your avatar picture</label>
                <input style="color: transparent;" type="file" name="avatar" id="avatarInput" onchange="changeAvatar(this);"/>
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
            <select class="form-control" name="companyCity">
                <option value="Sydney Metro">Sydney Metro</option>
            </select>
        </div>
        <div class="form-group">
            <label>Company Country</label>
            <select class="form-control" name="companyCountry">
                <option value="Australia">Australia</option>
            </select>
        </div>

        <div class="form-group action">
            <button class="btn btn-primary" id="save">Save changes</button>
        </div>
    </form>
</div>
{% include "settings/_settingsJs.volt" %}
