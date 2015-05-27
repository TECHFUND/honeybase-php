(function(){
  var honeybase = new HoneyBase("http://localhost:8000");
  var UserDB = honeybase.db("users_tbl");
  var chance = new Chance();

  /* ボタンとリストをセット */
  $("body").append("<button id='push'>push</button>");
  $("body").append('<table id="users" width="80%" height="200" border="1"><tr>'+
    '<td>id</td>'+
    '<td>name</td>'+
    '<td>age</td>'+
    '<td>job</td>'+
    '<td>address</td>'+
    '<td> </td>'+
    '<td> </td>'+
  '</tr></table>');

  /* 一覧表示 */
  UserDB.select({}).done(function(err, data){
    data.map(function(datum){
      render(datum);
      return true;
    });

    /*更新ボタン*/
    $(".update").click(function(e){
      UserDB.update(clickedID(e), {name:"Peaske", age:27, job:"CEO", address:"Shibuya"}, function(flag, data){
        if(flag) location.reload();
      });
    });

    /*削除ボタン*/
    $(".delete").click(function(e){
      UserDB.delete(clickedID(e), function(flag, data){
        if(flag) location.reload();
      });
    });
  });

  /* click時にpushして更新 */
  $("#push").click(function(e){
    UserDB.insert({name: chance.name(), age: chance.age(), job: chance.cc_type(), address: chance.city()}, function(flag, data){
      if(flag) location.reload();
    });
  });

  function render(datum){
    $("#users").append("<tr><td class='_id'>"+datum.id+
      "</td><td>"+datum.name+
      "</td><td>"+datum.age+
      "</td><td>"+datum.job+
      "</td><td>"+datum.address+
      "</td><td><button class='update'>update</button>"+
      "</td><td><button class='delete'>delete</button>"+
    "</td></tr>");
  }

  function rand(a, b){
    return a + Math.floor( Math.random() * (b - a + 1) );
  }

  function clickedID(e){
    var target_id_str = $(e.target.parentNode.parentNode).find("._id").text();
    var id = parseFloat(target_id_str.replace(',',''));
    return id;
  }

}());
