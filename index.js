window.onload = function(){
    let addnewbutton = document.getElementById('addnew')
    if (addnewbutton != null){
        addnewbutton.onclick = function(){
            location.href = "?act=addnew";
        }
    }
    let buttonAddPhone = document.getElementById('buttonAddPhone')
    if (buttonAddPhone != null){
        buttonAddPhone.onclick = function(){
            console.log('button addphone click')
            let child = document.createElement('div')
            child.innerHTML = '<div class="input-group"><input type="text" name="phone[]" class="form-control" placeholder="телефон" aria-label="телефон"></div>'
            document.getElementById('phonetd').appendChild(child)
        }
    }
    
    [].forEach.call(document.getElementsByClassName('delete_button'), function (elem) {
        elem.onclick = function(elem){
            let id = this.id.split('_')[1];
            location.href = "?act=delete&id="+id
        }
    });
}