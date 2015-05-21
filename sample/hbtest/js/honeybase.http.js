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

	var honeyCumbSocket = null;
	var honeybase_local = {
		timestamp : null,
		id_header : null
	};
	function HoneyBase(host, cb) {
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
              $_ajax("POST", self.api+"/oauth/login", params, function(data){
                cb(null, data);
              });
  		      } else {
   		        console.log('User cancelled login or did not fully authorize.');
              cb(1, null);
  		      }
  		    });
        } else {
          console.log("Already logged in");
          self.logout();
          cb(2, null);
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
		logout : function(cb) {
			var self = this;
			var params = {};
	    FB.getLoginStatus(function(response) {
		    FB.logout(function(response) {
  				$_ajax("POST", self.api + "/logout", params, function(data) {
						if(cb) cb(data.err);
  				});
        });
      });
		},
		getCurrentUser : function(cb) {
	    FB.getLoginStatus(function(response) {
        if(response.status == "connected"){
  				$_ajax("GET", self.api + "/get_current_user", params, function(data) {
  					if(data.user) cb(null, data.user);
  					else cb(2, null);
          });
        } else {
          cb(1, null);
        }
			});
		},
		publish: function(channel, value, cb) {
			var params = { channel : channel, value : value};
      socket.publish(cb);
		},
    subscribe: function(channel, cb){
      socket.subscribe(cb);
    },
		db : function(path) {
			return new DataBase(path, this.api);
		},
		ping: function(path, cb) {
      if(!cb) cb = path;
      if(!path) path = "";
      $_ajax("GET", this.host+path, {}, cb);
    }
	}

	function DataBase(path, api) {
		this.path = pathutil.norm(path);
    this.db = api+"/db";
		this.data = null;
	}

	DataBase.prototype = {
		push: function(value, cb) {
      var self = this;
			if(value.hasOwnProperty("id")) throw new Error("cannot set id in value object");
      var idGenerator = new IdGenerator();
			var id = idGenerator.getNextId();
			var params = { path : this.path, value : value, id : id};
      $_ajax("POST", self.db+"/push", params, function(data){
        data.value = JSON.parse(data.value);
        cb(data);
      });
		}
		,pushWithPriority: function(value, priority, onComplete) {
			value._priority = priority;
			return this.push(value, onComplete);
		}
		,remove: function(id, onComplete) {
			var params = { path : this.path + "/" + id};
			honeyCumbSocket.send_and_callback("unset",params,onComplete);
		}
		,set: function(id, value, onComplete) {
			var path = this.path;
			if(typeof id == "object") {
				onComplete = value;
				value = id;
			}else if(typeof id == "string"){
				path = path + "/" + id;
			}else{
				throw new Error("invalid parameter type");
			}
			if(typeof value != "object") {
				throw new Error("value must be object");
			}
			if(value.hasOwnProperty("id")) throw new Error("cannot set id in value object");
			var params = { path : path, value : value};
			honeyCumbSocket.send_and_callback("set", params, onComplete);
		}
		,setPriority : function(id, priority, onComplete) {
			var params = {
				path : this.path + "/" + id,
				value : {
					_priority : priority
				}
			};
			honeyCumbSocket.send_and_callback("set", params, onComplete);
		}
		,query: function(query) {
			var params = { path : this.path, query : query};
			return new Query(params);
		},
    select: function(query){
      this.query(query);
    }
		,get: function(id, cb) {
			var path = this.path;
			if(typeof id == "function") {
				cb = id;
			}else if(typeof id == "string") {
				path = path + "/" + id;
			}else{
				throw new Error("invalid id type");
			}
			var params = { path : path };
			honeyCumbSocket.send_and_callback("get",params,function(data) {
				cb(data);
			});
		}
		,getPath: function() {
			return this.path;
		}
		,parent: function() {
			return new DataStore(pathutil.parent(this.path), this.accessToken);
		}
		,child: function(query) {
			return new DataStore(pathutil.norm(this.path + "/" + query), this.accessToken);
		}
		,root: function() {
			return new DataStore("/",this.accessToken);
		}
	}
	function Query(params) {
		this.params = params;
		this.params.option = { };
	}
	Query.prototype = {
		done: function(cb) {
			honeyCumbSocket.send_and_callback("fm",this.params,function(data) {
				cb(data);
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

	var pathutil = {
			norm : function(path) {
				  var a = path.split('/');
				  var b = [];
				  for(var i=0;i < a.length;i++) {
				    if(a[i] != '') {
				      b.push(a[i]);
				    }
				  }
				  return b.join('/');
			},
			parent : function(path) {
				  var a = path.split('/');
				  a.pop();
				  return a.join('/');
			},
			path_name : function(path) {
				return path.indexOf("/")
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
  	xhr.withCredentials = false;
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
