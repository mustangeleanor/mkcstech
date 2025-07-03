<?php
/*
 *  Copyright (C) 2018 Laksamadi Guko.
 *  Créditos adicionais: Robson Alves - Cs Tech
 *
 *  Este programa é software livre; você pode redistribuí-lo e/ou modificá-lo
 *  sob os termos da Licença Pública Geral GNU conforme publicada pela
 *  Free Software Foundation; tanto a versão 2 da Licença, como (a seu critério)
 *  qualquer versão posterior.
 *
 *  Este programa é distribuído na expectativa de ser útil,
 *  mas SEM NENHUMA GARANTIA; sem mesmo a garantia implícita de
 *  COMERCIALIZAÇÃO ou ADEQUAÇÃO A UM DETERMINADO FIM. Veja a
 *  Licença Pública Geral GNU para mais detalhes.
 *
 *  Você deve ter recebido uma cópia da Licença Pública Geral GNU
 *  junto com este programa. Se não, veja <http://www.gnu.org/licenses/>.
 */
session_start();
// ocultar todos os erros
error_reporting(0);

ob_start("ob_gzhandler");

// checar url
$url = $_SERVER['REQUEST_URI'];

// carregar sessão MikroTik
$session = $_GET['session'];
$id = $_GET['id'];
$c = $_GET['c'];
$router = $_GET['router'];
$logo = $_GET['logo'];

$ids = array(
  "editor",
  "uplogo",
  "settings",
);

// idioma
include('./lang/isocodelang.php');
include('./include/lang.php');
include('./lang/'.$langid.'.php');

// quick bt
include('./include/quickbt.php');

// tema
include('./include/theme.php');
include('./settings/settheme.php');
include('./settings/setlang.php');
if ($_SESSION['theme'] == "") {
    $theme = $theme;
    $themecolor = $themecolor;
  } else {
    $theme = $_SESSION['theme'];
    $themecolor = $_SESSION['themecolor'];
}

// carregar configurações
include_once('./include/headhtml.php');
include('./include/config.php');
include('./include/readcfg.php');

include_once('./lib/routeros_api.class.php');
include_once('./lib/formatbytesbites.php');
?>
    
<?php
if ($id == "login" || substr($url, -1) == "p") {

  if (isset($_POST['login'])) {
    $user = $_POST['user'];
    $pass = $_POST['pass'];
    if ($user == $useradm && $pass == decrypt($passadm)) {
      $_SESSION["mikhmon"] = $user;

        echo "<script>window.location='./admin.php?id=sessions'</script>";
    
    } else {
      $error = '<div style="width: 100%; padding:5px 0px 5px 0px; border-radius:5px;" class="bg-danger"><i class="fa fa-ban"></i> Alerta!<br>Usuário ou senha inválidos.</div>';
    }
  }
  

  include_once('./include/login.php');
} elseif (!isset($_SESSION["mikhmon"])) {
  echo "<script>window.location='./admin.php?id=login'</script>";
} elseif (substr($url, -1) == "/" || substr($url, -4) == ".php") {
  echo "<script>window.location='./admin.php?id=sessions'</script>";

} elseif ($id == "sessions") {
  $_SESSION["connect"] = "";
  include_once('./include/menu.php');
  include_once('./settings/sessions.php');
  /*echo '
  <script type="text/javascript">
    document.getElementById("sessname").onkeypress = function(e) {
    var chr = String.fromCharCode(e.which);
    if (" _!@#$%^&*()+=;|?,~".indexOf(chr) >= 0)
        return false;
    };
    </script>';*/
} elseif ($id == "settings" && !empty($session) || $id == "settings" && !empty($router)) {
  include_once('./include/menu.php');
  include_once('./settings/settings.php');
  echo '
  <script type="text/javascript">
    document.getElementById("sessname").onkeypress = function(e) {
    var chr = String.fromCharCode(e.which);
    if (" _!@#$%^&*()+=;|?,~".indexOf(chr) >= 0)
        return false;
    };
    </script>';
} elseif ($id == "connect"  && !empty($session)) {
  ini_set("max_execution_time",5);  
  include_once('./include/menu.php');
  $API = new RouterosAPI();
  $API->debug = false;
  if ($API->connect($iphost, $userhost, decrypt($passwdhost))){
    $_SESSION["connect"] = "<b class='text-green'>Conectado</b>";
    echo "<script>window.location='./?session=" . $session . "'</script>";
  } else {
    $_SESSION["connect"] = "<b class='text-red'>Não Conectado</b>";
    $nl = '\n';
    if ($currency == in_array($currency, $cekindo['indo'])) {
      echo "<script>alert('Mikhmon não conectado!".$nl."Por favor verifique o IP, Usuário, Senha e a porta da API deve estar habilitada.".$nl."Se estiver usando conexão VPN, certifique-se que a VPN está conectada.')</script>";
    }else{
      echo "<script>alert('Mikhmon não conectado!".$nl."Por favor, verifique o IP, Usuário, Senha e a porta da API deve estar habilitada.')</script>";
    }
    if($c == "settings"){
      echo "<script>window.location='./admin.php?id=settings&session=" . $session . "'</script>";
    }else{
      echo "<script>window.location='./admin.php?id=sessions'</script>";
    }
  }
} elseif ($id == "uplogo"  && !empty($session)) {
  include_once('./include/menu.php');
  include_once('./settings/uplogo.php');
} elseif ($id == "reboot"  && !empty($session)) {
  include_once('./process/reboot.php');
} elseif ($id == "shutdown"  && !empty($session)) {
  include_once('./process/shutdown.php');
} elseif ($id == "remove-session" && $session != "") {
  include_once('./include/menu.php');
  $fc = file("./include/config.php" );
  $f = fopen("./include/config.php", "w");
  $q = "'";
  $rem = '$data['.$q.$session.$q.']';
  foreach ($fc as $line) {
    if (!strstr($line, $rem))
      fputs($f, $line);
  }
  fclose($f);
  echo "<script>window.location='./admin.php?id=sessions'</script>";
} elseif ($id == "about") {
  include_once('./include/menu.php');
  include_once('./include/about.php');
} elseif ($id == "logout") {
  include_once('./include/menu.php');
  echo "<b class='cl-w'><i class='fa fa-circle-o-notch fa-spin' style='font-size:24px'></i> Saindo...</b>";
  session_destroy();
  echo "<script>window.location='./admin.php?id=login'</script>";
} elseif ($id == "remove-logo" && $logo != ""  && !empty($session)) {
  include_once('./include/menu.php');
  $logopath = "./img/";
  $remlogo = $logopath . $logo;
  unlink("$remlogo");
  echo "<script>window.location='./admin.php?id=uplogo&session=" . $session . "'</script>";
} elseif ($id == "editor"  && !empty($session)) {
  include_once('./include/menu.php');
  include_once('./settings/vouchereditor.php');
} elseif (empty($id)) {
  echo "<script>window.location='./admin.php?id=sessions'</script>";
} elseif(in_array($id, $ids) && empty($session)){
	echo "<script>window.location='./admin.php?id=sessions'</script>";
}
?>
<script src="js/mikhmon-ui.<?= $theme; ?>.min.js"></script>
<script src="js/mikhmon.js?t=<?= str_replace(" ","_",date("Y-m-d H:i:s")); ?>"></script>
<?php include('./include/info.php'); ?>

<!--
  Créditos adicionais: Robson Alves - Cs Tech
-->
</body>
</html>
