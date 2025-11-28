  <a href="/">Home</a>
  <?php
  if ($isadmin) {
    echo ("    <div class=\"dropdown\">\n");
    echo ("      <button class=\"dropbtn\">Administration\n");
    echo ("        <i class=\"fa fa-caret-down\"></i>\n");
    echo ("      </button>\n");
    echo ("      <div class=\"dropdown-content\">\n");
    echo ("        <a href=\"/admin/users/\">Users</a>\n");
    echo ("        <a href=\"/admin/settings/\">Settings</a>\n");
    echo ("      </div>\n");
    echo ("    </div>\n");
  }
  ?>
  <a href="/logout">Logout</a>