/***********************************
 * SUPER DUPER CONVINIENT VARS&FUNCS
 ***********************************/
(function(global){
  global.honeybase = new HoneyBase("http://localhost:8000");
  global.UserDB = honeybase.db("users_tbl");
  global.chance = new Chance();



  function AI (){}
  AI.renderUserList = function (datum){
    $("#users").append("<tr><td class='_id'>"+datum.id+
      "</td><td>"+datum.name+
      "</td><td>"+datum.age+
      "</td><td>"+datum.job+
      "</td><td>"+datum.address+
      "</td><td><button class='update'>update</button>"+
      "</td><td><button class='delete'>delete</button>"+
    "</td></tr>");
  }
  AI.rand = function(a, b){
    return a + Math.floor( Math.random() * (b - a + 1) );
  }
  AI.clickedID = function(e){
    var target_id_str = $(e.target.parentNode.parentNode).find("._id").text();
    var id = parseFloat(target_id_str.replace(',',''));
    return id;
  }
  AI.afterReload = function(flag){
    if(flag) location.reload();
    else alert("something wrong");
  }
  global.AI = AI;


  return global;
}(window));







/*
* View Component
*/
(function(global){

  function Index(){}

  Index.loginView = function(){

    $("body").append("<button id='push'>push</button>");
    $("body").append("<button id='logout'>logout</button>");
    $("body").append('<table id="users" width="80%" height="200" border="1"><tr>'+
      '<td>id</td>'+
      '<td>name</td>'+
      '<td>age</td>'+
      '<td>job</td>'+
      '<td>address</td>'+
      '<td> </td>'+
      '<td> </td>'+
    '</tr></table>');

    /***********************************
     * MAIN FUNCTIONS
     ***********************************/
    /* 一覧表示 */
    UserDB.select({}).done(function(flag, data){
      data.map(function(datum){
        AI.renderUserList(datum);
        return true;
      });

      /*更新ボタン*/ //schemeが違ったり変更がなかったりidがなかったりするとflagがfalse
      $(".update").click(function(e){
        var rand_data = {name: chance.name(), age: chance.age(), job: chance.cc_type(), address: chance.city()};
        UserDB.update(AI.clickedID(e), rand_data, function(flag, data){
          AI.afterReload(flag);
        });
      });

      /*削除ボタン*/
      $(".delete").click(function(e){
        UserDB.delete(AI.clickedID(e), function(flag, data){
          AI.afterReload(flag);
        });
      });
    });

    /* click時にpushして更新 */
    $("#push").click(function(e){
      var rand_data = {name: chance.name(), age: chance.age(), job: chance.cc_type(), address: chance.city()};
      UserDB.insert(rand_data, function(flag, data){
        AI.afterReload(flag);
      });
    });

    $("#logout").click(function(e){
      honeybase.logout(function(flag){
        console.log('logged out');
        AI.afterReload(flag);
      });
    });
  }


  Index.logoutView = function(){
    $("body").append("<button id='oauth'>oauth</button>");
    $("#oauth").click(function(e){
      honeybase.auth("facebook", function(flag, user){
        console.log(flag, user);
        AI.afterReload(flag);
      });
    });
  }


  global.Index = Index;
  return global;
}(window));














/*
* MAIN
*/
(function(){
  honeybase.current_user(function(isLoggedIn, user){
    if(isLoggedIn) Index.loginView();
    else Index.logoutView();
  });
}());
