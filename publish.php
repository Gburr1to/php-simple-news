<?php 
include_once 'header.php';
$needs_auth = true; // Ta stran zahteva prijavo, če bi kdo poslal post brez, da bi bil loggan in
                    //v headerju je pogoj
?>

<!--
#obrazec za vnos naslova, povzetka in vsebine novice.
#insert v sql database
 - prečisti vnos
 - preveri, da ni articla z istim imenom
 - prevzemi user_ID
 - user_ID, naslov, povzetek in vsebino podaj kot argument za dodajanje v sql

 možne težave: datetime, submit_article header
 - DELA OBOJE
-->

<?php
// predelam, da preveri, če article s tem imenom obstaja 
function article_exists($article): bool
{
	global $conn;
	$article = mysqli_real_escape_string($conn, $article);
	$query = "SELECT * FROM articles WHERE title='$article'";
	$res = $conn->query($query);
	return mysqli_num_rows($res) > 0;
}

//predelam, da doda article v sql 
function submit_article($title, $abstract, $text): bool
{
    global $conn;
    $title = mysqli_real_escape_string($conn, $title);
    $abstract = mysqli_real_escape_string($conn, $abstract);
    $text = mysqli_real_escape_string($conn, $text);
    //$date = date("Y-m-d H:i:s");
    $user_id = $_SESSION["USER_ID"];

    $query = "INSERT INTO articles (title, abstract, text, date, user_id) 
            VALUES ('$title', '$abstract', '$text', NOW(), '$user_id');";
    if($conn->query($query)){
    return true;
    }
    else{
    echo mysqli_error($conn);
    return false;
    }
}
//validacija
$error = "";
if(isset($_POST["article-title"])){
    //VALIDACIJA: preveriti moramo, ali je uporabnik pravilno vnesel podatke (unikatno uporabniško ime, dolžina gesla,...)
    //Preveri če so vsi podatki izpolnjeni
    if(empty($_POST["article-title"]) || empty($_POST["article-story"]) || empty($_POST["article-abstract"])){
        $error = "Izpolnite vse podatke.";
    }
    //Preveri ali ime objave obstaja
    else if(article_exists($_POST["article-title"])){
        $error = "Ime novice je že zasedeno.";
    }
    //Podatki so pravilno izpolnjeni, registriraj uporabnika
    else if(submit_article($_POST["article-title"], $_POST["article-abstract"], $_POST["article-story"])){
        header("Location: " . BASE_URL); //če ne deluje, moram dodati include_once 'root.php';
        die();
    }
    //Prišlo je do napake pri objavi
    else{
        $error = "Prišlo je do napake med objavo novice.";
    }
}

?>

<!-- obrazec za vnos polj --> <!-- sam sebi pošljem POST -->
<div class="container">
    <h3 class="mb-3">Objavi novico</h3>
    <form action="publish.php" method="POST">
        <div class="mb-3">
            <label for="article-title" class="form-label">Naslov</label>
            <input type="text" class="form-control" id="article-title" name="article-title" value="<?php echo isset($_POST["article-title"]) ? $_POST["article-title"]: ""; ?>">
        </div>
        <div class="mb-3">
            <label for="article-abstract" class="form-label">Povzetek</label>
            <textarea class="form-control" id="article-abstract" name="article-abstract" rows="5"><?php echo isset($_POST["article-abstract"]) ? htmlspecialchars($_POST["article-abstract"]) : ""; ?></textarea>
        </div>
        <div class="mb-3">
            <label for="article-story" class="form-label">Vsebina novice</label>
            <textarea class="form-control" id="article-story" name="article-story" rows="5"><?php echo isset($_POST["article-story"]) ? htmlspecialchars($_POST["article-story"]) : ""; ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary" name="submit-article">Objavi novico!</button>
        <label class="text-danger"><?php echo $error; ?></label>
    </form>
</div>

<?php
include_once 'footer.php';
?>