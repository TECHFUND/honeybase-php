(function(){
  var hb = new HoneyBase("http://localhost:8000");

  hb.db("users").push({name:"Shogo", age: 24, job:"engineer", address: "Setagaya"}, function(data){
    console.log(data);
  });

  /*
  hb.db("users").select({name:"Shogo"}).done(function(data){
    console.log(data);
  });
  */

}());
