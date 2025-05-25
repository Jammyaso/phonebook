<?php 

global $db;
$db = new SQLite3('phonebook.db');

Class PhoneBook{

    public $id = 0;
    public $email = ""; //unique
    public $firstName;
    public $secondName = "";
    public $thirdName = "";
    public $phones = []; //unique
    public $timestamp = 0;
    public $text = "";
    public function showAsArray(): array{
        return array(
            ["id"] => $this->id,
            ["email"] => $this->email,
            ["firstName"] => $this->firstName,
            ["secondName"] => $this->secondName,
            ["thirdName"] => $this->thirdName,
            ["timestamp"] => $this->timestamp,
            ["phones"] => $this->phones,
            ["text"] => $this->text
        );
    }
    public function getList(){
        global $db;
        $outarray = [];
        $query = "SELECT id FROM phonebook";
        $result = $db->query($query);
        while ($row = $result->fetchArray(SQLITE3_ASSOC)){
            $outarray[] = $row["id"];
        }
        return $outarray;
    }
    public function getData($id = null): void{
        global $db;
        $query = "SELECT * FROM phonebook";
        if ($id) {
            $query = $query." WHERE id=".$id;
        }
        $result = $db->query($query);
        $row = $result->fetchArray(SQLITE3_ASSOC);
        if ($row){
            $this->id = $row["id"];
            $this->email = $row["email"];
            $this->firstName = $row["firstname"];
            $this->secondName = $row["secondname"];      
            $this->thirdName = $row["thirdname"];
            $this->timestamp = $row["timestamp"];
            $this->text = $row["text"];
        }

        $result = $db->query("SELECT phones.phone, phones.id, phonestypes.id as phonestypeid FROM phones, phoneslist, phonestypes WHERE phoneslist.contact = ".$this->id." AND phones.id = phoneslist.phone AND phonestypes.id = phones.type");
        $this->phones = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)){
            //var_dump($row);
            $this->phones[$row['id']] = [$row['phone'], $row['phonestypeid']];            
        }         
    }
    public function loadValues(){
        global $db;
        //check unique
        if (!empty($_POST['email'])){
            $result = $db->query("SELECT email FROM phonebook WHERE email = '".$_POST['email']."'");
            if ($row = $result->fetchArray(SQLITE3_ASSOC)){
                return print("Error: email already exist!");
            }
        }
        if (!empty($_POST['phone'])){
            //print_r("'".implode("','",$_POST['phone'])."'");
            $this->phones = $_POST['phone'];
            $result = $db->query("SELECT id FROM phones WHERE phone in ('".implode("','",$this->phones)."')");
            if ($row = $result->fetchArray(SQLITE3_ASSOC)){
                return print("Error: phone already exist!");
            }
        }
        
        $this->email = $_POST['email'];
        $this->firstName = $_POST['firstName'];
        $this->secondName = $_POST['secondName'];
        $this->thirdName = $_POST['thirdName'];
        $this->text = $_POST['text'];
        $this->timestamp = time();
        //insert into db
        $query = "INSERT INTO phonebook (email, firstname, secondname, thirdname, timestamp, text) VALUES ('".$this->email."', '".$this->firstName."', '".$this->secondName."', '".$this->thirdName."', '".$this->timestamp."', '".$this->text."')";
        //print_r($query);
        $result = $db->exec($query);
        $lastinsertedid = $db->lastInsertRowID();

        //work around phones
        foreach ($this->phones as $phone){
            $query = "INSERT INTO phones (phone) VALUES ('".$phone."')";
            $result = $db->exec($query);
            $lastphoneid = $db->lastInsertRowID();
            $query = "INSERT INTO phoneslist (contact, phone) VALUES ('".$lastinsertedid."','".$lastphoneid."')";
            $result = $db->exec($query);
        }
        

        if ($result) print('Ok, inserted');
    }
    public function delete($id = null){
        global $db;
        $query = "SELECT phone FROM phoneslist WHERE contact = '".$id."'";
        $result = $db->query($query);
        $phoneslist = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)){            
            $phoneslist[] = $row['phone'];
        }  
        
        $query = "DELETE FROM phoneslist WHERE contact = ".$id.";";
        $query .= "DELETE FROM phones WHERE id in ('" . implode("','",$phoneslist) . "');";
        $query .= "DELETE FROM phonebook WHERE id = ".$id.";";
        $db->exec($query);
        print("deleted");

    }
}

//rounting


//print('<pre>');


$phonetype = [1 => 'Мобильный', 2 => 'Рабочий'];
?>
<!DOCTYPE html>
<html>
    <head>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
        <link rel="stylesheet" href="index.css">
        <link rel="icon" type="image/png" href="favicon.png">
        <title>PhoneBook</title>
        <script type="text/javascript" src="index.js"></script>
    </head>
    <body>
        <div id="main" class="d-flex flex-row mb-2 align-items-stretch vh-100">
            <div id="leftColumn" class="p-2"><div><h1>Телефонная книга</h1></div>
                <div><a href="?act=" class="link-offset-2 link-offset-3-hover link-underline link-underline-opacity-0 link-underline-opacity-75-hover">Начальная страница</a></div>
                <div><a href="?act=addnew" class="link-offset-2 link-offset-3-hover link-underline link-underline-opacity-0 link-underline-opacity-75-hover">Добавить новую запись</a></div>
            </div>
            
            <div id="content" class="p-2">
            <?php 

if (empty($_GET['act'])) $_GET['act'] = '';

switch($_GET['act']){
    case "":{ ?>



            <button id="addnew" type="button" class="btn btn-success">
<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-add" viewBox="0 0 16 16">
  <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m.5-5v1h1a.5.5 0 0 1 0 1h-1v1a.5.5 0 0 1-1 0v-1h-1a.5.5 0 0 1 0-1h1v-1a.5.5 0 0 1 1 0m-2-6a3 3 0 1 1-6 0 3 3 0 0 1 6 0M8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4"></path>
  <path d="M8.256 14a4.5 4.5 0 0 1-.229-1.004H3c.001-.246.154-.986.832-1.664C4.484 10.68 5.711 10 8 10q.39 0 .74.025c.226-.341.496-.65.804-.918Q8.844 9.002 8 9c-5 0-6 3-6 4s1 1 1 1z"></path>
</svg> Добавить запись
              </button>
                <table class="table table-hover ">
                    <thead>
                        <tr>
                            <td hidden>id</td>
                            <td>ФИО</td>
                            <td>телефон</td>
                            <td>e-mail</td>
                            <td>примечание</td>
                            <td>дата доб.</td>
                            <td></td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        //list of records
                        $element = new PhoneBook();
                        foreach($element->getList() as $row){
                            $element->getData($row);
                        
                        ?>
                        <tr>
                            <td hidden><input type="hidden" name="id" value="<?php print($element->id)?>"></td>
                            <td>
                                <input type="text" name="fio" class="form-control" placeholder="ФИО" aria-label="ФИО" aria-describedby="basic-addon1" 
                                    value="<?php print($element->firstName." ".$element->secondName." ".$element->thirdName);?>" readonly>
                            </td>
                            <td>
                                <ul class="list-group">
                                    
                                
                                <?php
                            
                                foreach($element->phones as $phone){  
                                    print('<li class="list-group-item">'.$phone[0].' ('.$phonetype[$phone[1]].')</li>');
                                }
                            ?></ul></td>
                            <td><input type="text" name="email" class="form-control" placeholder="email" aria-label="email" 
                            value="<?php print($element->email);?>" readonly></td>
                            <td><input type="text" class="form-control" value="<?php print($element->text)?>" readonly></td>
                            <td><input type="datetime" name="timestamp" class="form-control" value ="<?php print(date('Y-m-d H:i:s',$element->timestamp)) ?>"  readonly></td>
                            <td><button type="button" class="btn btn-primary">
<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pen" viewBox="0 0 16 16">
  <path d="m13.498.795.149-.149a1.207 1.207 0 1 1 1.707 1.708l-.149.148a1.5 1.5 0 0 1-.059 2.059L4.854 14.854a.5.5 0 0 1-.233.131l-4 1a.5.5 0 0 1-.606-.606l1-4a.5.5 0 0 1 .131-.232l9.642-9.642a.5.5 0 0 0-.642.056L6.854 4.854a.5.5 0 1 1-.708-.708L9.44.854A1.5 1.5 0 0 1 11.5.796a1.5 1.5 0 0 1 1.998-.001m-.644.766a.5.5 0 0 0-.707 0L1.95 11.756l-.764 3.057 3.057-.764L14.44 3.854a.5.5 0 0 0 0-.708z"></path>
</svg></button>
<button type="button" class="btn btn-outline-danger delete_button" id="delete_<?php print($element->id)?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
  <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"></path>
  <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"></path>
</svg></button>
                            </td>
                        </tr>
                        <?php 
                        //end of list
                            }
                        ?>
                    </tbody>
                </table> 
<?php
}; break;
case "addnew":{
    if (!empty($_POST)){
        
        $element = new PhoneBook();
        $element->loadValues();
    }
?>
<h3>Добавить новую запись</h3>
<form action="" method="post" class="">
<table class="table table-hover ">
<tr><td><input type="text" name="firstName" class="form-control" placeholder="Имя" aria-label="Имя" ></td></tr>
<tr><td><input type="text" name="secondName" class="form-control" placeholder="Фамилия" aria-label="Фамилия" ></td></tr>
<tr><td><input type="text" name="thirdName" class="form-control" placeholder="Отчество" aria-label="Отчество" ></td></tr>
<tr><td><input type="text" name="email" class="form-control" placeholder="почта" aria-label="почта" ></td></tr>
<tr><td id="phonetd">
<div class="input-group mb-2">
  <input type="text" name="phone[]" class="form-control" placeholder="телефон" aria-label="телефон">
  <div class="input-group-append">
    
    <button id="buttonAddPhone" type="button" class="btn btn-secondary">
<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus" viewBox="0 0 16 16">
  <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"></path>
</svg>
              </button>
  </div>
  </div>
</td></tr>
<tr><td><input type="text" name="text" class="form-control" placeholder="примечание" aria-label="примечание" ></td></tr>
<tr><td><input type="submit" name="submit" class="form-control"  ></td></tr>
</table>
</form>

<?php }; break;
case "delete":{
    $element = new PhoneBook();
    $element->delete($_GET['id']);
}

}
?>               
            </div>
        </div>
    </body>
    
</html>
