(function(){
  var hb = new HoneyBase("http://localhost:8000");

  hb.db("users").push({}, function(data){
    console.log(data);
  });

}());
