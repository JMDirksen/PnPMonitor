<?php
require_once('init.php');
global $s;

// Check session activate link
if (isset($_GET['activate']) && isset($_GET['user'])) {
  $user = getUser($_GET['user']);
  if ($user) {
    if ($user->activate == $_GET['activate']) {
      $s->set('user', $user->id);
      unset($user->activate);
      updateUser($user);
      saveDb();
      redirect("?p=login&msg=Logged in");
    }
  }
  redirect("?p=login&err=Login link invalid");
}

// Request session activate link
if (isset($_POST['loginForm'])) {
  $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
  $user = getUser($email);
  if ($user) {
    $user->activate = newSecret();
    updateUser($user);
    saveDb();
    sendMail(
      $user->email,
      "PnPMonitor login link",
      "PnPMonitor login link: \n" .
        sessionActivateLink($user->activate, $user->id)
    );
    redirect("?p=login&msg=An email with a login link has been sent");
  } elseif (!count($db->users)) {
    $user = newUser($email);
    updateUser($user);
    saveDb();
    $s->set('user', $user->id);
    redirect("?p=settings&msg=Welcome, please configure email settings");
  } else redirect("?p=login&err=User does not exist");
}

// Login check
loginRequired();
// --- Must be logged in for below actions ---

// Save monitor
if (isset($_POST['saveMonitor'])) {
  $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
  if (!$name) redirect("?p=edit&id=" . $_POST['id'] . "&err=Invalid name");
  if ($_POST['type'] == "page") {
    $url = filter_var($_POST['field1'], FILTER_VALIDATE_URL);
    $text = filter_var($_POST['field2'], FILTER_SANITIZE_STRING);
    if (!$url) redirect("?p=edit&id=" . $_POST['id'] . "&err=Invalid url");
    $monitor = pageMonitor($name, $url, $text);
  } elseif ($_POST['type'] == "port") {
    $host = filter_var(
      $_POST['field1'],
      FILTER_VALIDATE_DOMAIN,
      FILTER_FLAG_HOSTNAME
    );
    $port = filter_var($_POST['field2'], FILTER_VALIDATE_INT);
    if (!$host) redirect("?p=edit&id=" . $_POST['id'] . "&err=Invalid host");
    if (!$port) redirect("?p=edit&id=" . $_POST['id'] . "&err=Invalid port");
    $monitor = portMonitor($name, $host, $port);
  }
  if ($_POST['id'] == "new") addMonitor($monitor);
  else {
    $monitor->id = (int)$_POST['id'];
    editMonitor($monitor);
  }
  saveDb();
  redirect("?p=monitor&id=" . $_POST['id']);
}

// Save settings
if (isset($_POST['settingsForm'])) {
  $db->settings->notify = $_POST['notify'];
  $db->settings->smtpHost = $_POST['smtpHost'];
  $db->settings->smtpSecure = $_POST['smtpSecure'];
  $db->settings->smtpPort = $_POST['smtpPort'];
  $db->settings->smtpUser = $_POST['smtpUser'];
  if (!empty($_POST['smtpPass'])) $db->settings->smtpPass = $_POST['smtpPass'];
  $db->settings->smtpFrom = $_POST['smtpFrom'];
  saveDb();
  redirect("?p=settings&msg=Changes saved");
}

// Delete monitor
if (isset($_GET['delete'])) {
  $monitor = getMonitor($_GET['delete']);
  deleteMonitor($monitor);
  saveDb();
  redirect("?p=monitors");
}

// New user
if (isset($_POST['newUser'])) {
  $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
  if (!$email) redirect("?p=users&err=Invalid email");
  $test = getUser($email);
  if ($test) redirect("?p=users&err=User already exists");
  newUser($email);
  saveDb();
  redirect("?p=users&msg=User added");
}

// Delete user
if (isset($_GET['delUser'])) {
  if (count($db->users) == 1)
    redirect("?p=users&err=Can not delete the only user, add a new user first");
  $user = getUser($_GET['delUser']);
  deleteUser($user);
  saveDb();
  redirect("?p=users&msg=User $user->email deleted");
}

// Logout
if (isset($_GET['logout'])) {
  $s->unset('user');
  redirect("?p=login&msg=Logged out");
}
