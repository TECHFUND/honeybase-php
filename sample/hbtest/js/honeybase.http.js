(function(global){
  var VERSION = "v1";

  /* for OAuth */
  FB.init({
    appId      : '759914587441162',
    status     : true,
    xfbml      : true,
    version    : 'v2.3' // or v2.0, v2.1, v2.0
  });

	var myconsole = {
		log : function(v) {
			if(window.console) console.log(v);
		}
	}

	var honeybase_local = {
		timestamp : null,
		id_header : null
	};
	function HoneyBase(host) {
		this.host = format_host(host);
    this.api = this.host + "api/" + VERSION;
	}
	window.HoneyBase = HoneyBase;

	var eventnames = {
			callback : "a",
			push : "b",
			set : "c",
			remove : "d",
			send : "e"
	}

	HoneyBase.Error = {
		AddAccount : {
			FormatError : "1",
			AlreadyExist : "2"
		},
		Login : {
			FormatError : "1",
			LoginError : "2",
			EmailNotVerificated : "3"
		}
	};

	HoneyBase.prototype = {
/************************************
 * OAUTH
 ************************************/
    auth : function(provider, option, cb){
      if(!cb) {
        cb = option;
        option = {};
      }
			var self = this;
	    FB.getLoginStatus(function(response) {
        if(response.status != "connected"){
  		    FB.login(function(response) {
  		      if (response.authResponse) {
              var params = {
                provider: provider,
                user_access_token: response.authResponse.accessToken,
                option: JSON.stringify(option)
              };
              $_ajax("POST", self.api+"/oauth", params, function(res){
                cb(res.flag, res.user);
              });
  		      } else {
   		        console.log('User cancelled login or did not fully authorize.');
              cb(false, null);
  		      }
  		    });
        } else {
          console.log("Already logged in");
          self.logout();
          cb(false, null);
        }
      });
    },
		authWithJWT : function(token, cb) {
			var self = this;
			var params = { token : token };
			$_ajax("POST", self.api+"/auth_with_jwt", params, function(data) {
				cb(data.err, data.user);
			});
		},
		logout : function(cb) {
			var self = this;
			var params = {};
	    FB.getLoginStatus(function(response) {
        params.social_id = response.authResponse.userID;
        setTimeout(function(){
	      FB.logout(function(response) {
				  $_ajax("POST", self.api + "/logout", params, function(res) {
						if(cb) cb(res.flag);
  				});
	      });
        }, 100);
      });
		},
		current_user : function(cb) {
			var self = this;
			var params = {};
	    FB.getLoginStatus(function(response) {
        if(response.status == "connected"){
  				$_ajax("GET", self.api + "/get_current_user", params, function(data) {
  					if(data.user) cb(true, data.user);
  					else cb(false, null);
          });
        } else {
          cb(false, null);
        }
			});
		},

    /*
		addAccount : function(email,secret,option,cb) {
      if(!cb) {
        cb = option;
        option = {};
      }
			var params = {  email : email, secret : secret, option : option};
			$_ajax("POST", self.api+"/signup", params, function(data) {
				cb(data.err, data.user);
			});
		},
		login : function(email, secret, cb) {
			var self = this;
			var params = { email : email, secret : secret };
			$_ajax("POST", self.api + "/login", params, function(data) {
				if(data.user) cb(null, data.user);
				else cb(1, null);
			});
		},
		anonymous : function(cb) {
			var self = this;
			$_ajax("POST", self.api + "/anonymous", params, function(data) {
				if(data.user) cb(null, data.user);
				else cb(1, null);
			});
		},
    */


/************************************
 * PUBSUB
 ************************************/
		publish: function(channel, value, cb) {
			var params = { channel : channel, value : value};
      socket.publish(cb);
		},
		pub: function(channel, value, cb) {
      this.publish(channel, value, cb);
		},
		send: function(channel, value, cb) {
      this.publish(channel, value, cb);
		},
    subscribe: function(channel, cb){
      socket.subscribe(cb);
    },
    sub: function(channel, cb){
      this.subscribe(channel, cb);
    },
    onsend: function(channel, cb){
      this.subscribe(channel, cb);
    },
		db : function(table) {
			return new DataBase(table, this.api);
		},
		ping: function(table, cb) {
      if(!cb) cb = table;
      if(!table) table = "";
      $_ajax("GET", this.host+table, {}, cb);
    }
	}

/************************************
 * DATABASE
 ************************************/
	function DataBase(table, api) {
		this.table = tableutil.norm(table);
    this.db = api+"/db";
		this.data = null;
	}

	DataBase.prototype = {
		insert: function(value, cb) {
      var self = this;
			if(value.hasOwnProperty("id")) throw new Error("cannot set id in value object");
      var idGenerator = new IdGenerator();
			var id = idGenerator.getNextId();
			var params = { table : this.table, value : value, id : id};
      $_ajax("POST", self.db+"/insert", params, function(res){
        var flag = res.flag;
        var data = res.data;
        data.value = JSON.parse(data.value);
        if(cb) cb(flag, data);
      });
		},
    push: function(value, cb){
      this.insert(value, cb);
    },
    delete: function(id, cb) {
			if(typeof id != "number") throw new Error("id must be number");
			var params = { id: id+"", table : this.table };
      $_ajax("POST", this.db+"/delete", params, function(res){
        if(cb) cb(res.flag, res.data);
      });
		},
    remove: function(id, cb){
      this.delete(id, cb);
    },
    update: function(id, value, cb) {
			if(typeof id != "number") throw new Error("id must be number");
			if(typeof value != "object") throw new Error("value must be object");
			if(value.hasOwnProperty("id")) throw new Error("cannot set id in value object");

			var params = { id: id+"", table : this.table, value : value};

      $_ajax("POST", this.db+"/update", params, function(res){
        if(cb) cb(res.flag, res.data);
      });
		},
    set: function(id, value, cb){
      this.update(id, value, cb);
    },
    select: function(query, cb){
      var self = this;
			var params = { table : self.table, value : query};
      var selector_obj = new Selector(params, self.db);
      if(cb) selector_obj.done(cb);
      return selector_obj;
    },
    query: function(q, cb) {
      return this.select(q, cb);
		}
	}

/************************************
 * SEARCH
 ************************************/
	function Selector(params, db) {
		this.params = params;
		this.params.option = { };
    this.db = db;
	}
	Selector.prototype = {
		done: function(cb) {
      $_ajax("GET", this.db+"/select", this.params, function(res){
        cb(res.flag, res.data);
      });
		}
		,skip: function(skip) {
			if(!(typeof skip == "number")) {
				throw new Error("invalid skip parameter.");
			}
			this.params.option.skip = skip;
			return this;
		}
		,sort: function(_mode) {
			var mode = _mode || "desc";
			if(mode == "asc") {
				this.params.option.sort = "_priority";
			}else if(mode == "desc") {
				this.params.option.desc = "_priority";
			}else{
				throw new Error("undefined sort mode.");
				myconsole.log("usage : sort(\"desc\")");
			}
			return this;
		}
		,asc : function() {
			return this.sort("asc");
		}
		,desc : function() {
			return this.sort("desc");
		}
		,desort: function(attr) {
			this.params.option.desc = attr;
			return this;
		}
		,limit: function(n) {
			this.params.option.limit = n;
			return this;
		}
	}

	var tableutil = {
			norm : function(table) {
				  var a = table.split('/');
				  var b = [];
				  for(var i=0;i < a.length;i++) {
				    if(a[i] != '') {
				      b.push(a[i]);
				    }
				  }
				  return b.join('/');
			},
			parent : function(table) {
				  var a = table.split('/');
				  a.pop();
				  return a.join('/');
			},
			table_name : function(table) {
				return table.indexOf("/")
			}
	}

	var shuffle_table = 'ybfghijam6cpqdrw71nx34eo5suz0t9vkl28';
	function IdGenerator() {
		this.timestamp = new Date().getTime();
		this.id_header = this.getHeader(this.timestamp);
		this.prev_id = 0;
	}
	IdGenerator.prototype = {
		init : function(ts) {
			this.timestamp = ts;
			this.id_header = this.getHeader(this.timestamp);
		},
		getHeader : function(t) {
			return t.toString(36);
		},
		getHash : function(time) {
			var h = '';
			var t = time;
			while(t > 0) {
				h += shuffle_table[t % 36];
				t = t/36;
				t = Math.floor(t);
			}
			return h;
		},
		getNextId : function() {
			this.prev_id++;
			return this.id_header + add04(this.prev_id.toString(36)) + shuffle_table[Math.floor(Math.random() * 36)] + shuffle_table[Math.floor(Math.random() * 36)] + shuffle_table[Math.floor(Math.random() * 36)];
			function add04(str) {
				var str2 = str;
				for(var i=0;i < 4-str.length;i++) {
					str2 = "0" + str2;
				}
		        return str2;
			}
		}
	}

	function format_host(host) {
		if(host[host.length - 1] === "/") {
			return host;
		}else{
			return host + "/";
		}
	}

	function $_ajax(method, url, params, cb) {
		var xhr = null;
		if(window.XMLHttpRequest) {
			xhr = new XMLHttpRequest();
		}else if( window.XDomainRequest ){
  		xhr = new XDomainRequest();
  	}
  	var params_str = querystring(params);
		if(method=="GET" && params_str != "") url += "?"+params_str;
  	xhr.open(method , url);
  	xhr.withCredentials = true;
  	xhr.onload = function() {
  		cb(JSON.parse(xhr.responseText));
  	}
  	xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  	xhr.send(params_str);

  	function querystring(params) {
  		var params_array = []
  		for(var key in params) {
        if(typeof params[key] == "string") params_array.push(key + "=" + encodeURIComponent(params[key]));
        if(typeof params[key] == "object") params_array.push(key+"="+JSON.stringify(params[key]));
  		}
  		return params_array.join("&");
  	}
  }
	HoneyBaseUtil = {
    open_dialog : function(provider, cb){
      var env = "dev";
			var auth_host = (env == "dev") ? "http://localhost:18000" : "http://preneur.io";
      var url = auth_host + "/account-api/oauth/dialog?"+
				"honey_redirect_uri=" + encodeURIComponent(window.location.href)+
				"&provider="+provider+
				"&display=popup"+
    		"response_type=granted_scopes";

      transport_window(url);

      function transport_redirect(url) {
        global.location = url;
      }
      function transport_window(url) {
        var window_features = {
          'menubar'    : 1,
          'location'   : 0,
          'resizable'  : 0,
          'scrollbars' : 1,
          'status'     : 0,
          'dialog'     : 1,
          'width'      : 150,
          'height'     : 150
        };

        var child = window.open(url, "Login", window_features);

        addListener(child, 'unload', function(e) {});

        addListener(window, 'message', function(e) {
          // get Json Web Token from dialog
          if( typeof e.data == "string"){
            cb(null, e.data);
          } else {
            cb(1, "Something wrong with post JWT message from the provider dialog");
          }
        });
      }

      function addListener(w, event, cb) {
        if (w['attachEvent']) w['attachEvent']('on' + event, cb);
        else if (w['addEventListener']) w['addEventListener'](event, cb, false);
      }
    }
  }
}(window));
