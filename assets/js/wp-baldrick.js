/* custom helpers */
if( typeof Handlebars !== 'undefined' ){
  var hb_find_field = function( obj, field ){
    
    if( typeof obj === 'object' && obj !== null ){

      // cool its an object with stuff
      for( var part in obj ){
        if( part === field || obj[ part ] === field ){
          return obj[ part ];
        }
      }
      // go deeper
      for( var part in obj ){
        if( typeof obj[ part ] === 'object' && obj[ part ] !== null ){
          return hb_find_field( obj[ part ], field );
        }
      }      
    }

    return false;
  }

/* custom helpers */
var handlebarsVariables = {};

Handlebars.registerHelper("even", function(options) {
  var intval = options.data.index / 2;
  if( intval === Math.ceil( intval ) ){
    return options.fn(this);
  }else{
    return false;
  }
  
});
Handlebars.registerHelper("odd", function(options) {
  var intval = options.data.index / 2;
  if( intval === Math.ceil( intval ) ){
    return false;
  }else{
    return options.fn(this);
  }
});
Handlebars.registerHelper("json", function(context) {
    return JSON.stringify( context );
});
Handlebars.registerHelper(":node_point", function(context) {

    if( this._node_point ){
      var nodes = this._node_point.split('.'),
          node_point_record = nodes.join('][') + ']',
          node_path = node_point_record.replace( nodes[0] + ']', nodes[0] );

      return new Handlebars.SafeString( '<input type="hidden" name="' + node_path + '[_id]" value="' + nodes[nodes.length-1] + '"><input type="hidden" name="' + node_path + '[_node_point]" value="' + this._node_point + '">' );
    }

});
Handlebars.registerHelper(":name", function(context) {

    if( this._node_point ){
      var nodes = this._node_point.split('.'),
          node_point_record = nodes.join('][') + ']',
          node_path = node_point_record.replace( nodes[0] + ']', nodes[0] );

      return node_path;
    }

});

  Handlebars.registerHelper('find', function(obj, field, options) {
      
      var value = hb_find_field( obj, field );

      if( false === value ){
        return options.inverse(this)
      }else{
        return options.fn( value );
      }

  });
  Handlebars.registerHelper("is_single", function(value, options) {
    if(Object.keys(value).length !== 1){
      return false;
    }else{
      return options.fn(this);
    }
  });
  Handlebars.registerHelper("script", function(options) {
    var atts = [];
    for( var att in options.hash ){
      atts.push( att + '="' + options.hash[att] + '"' );      
    }

    return '<script ' + atts.join(' ') + '>' + options.fn(this) + '</script>';
  });
  Handlebars.registerHelper("is", function(value, options) {

    if( options.hash.value ){
      if(options.hash.value === '@key'){
        options.hash.value = options.data.key;
      }
      if(options.hash.value === value){
        return options.fn(this);
      }else{
        if(this[options.hash.value]){
          if(this[options.hash.value] === value){
            return options.fn(this);
          }
        }
        return options.inverse(this);
      }
    }
    if( options.hash.not ){
      if(options.hash.not === '@key'){
        options.hash.not = options.data.key;
      }
      if(options.hash.not !== value){
        return options.fn(this);
      }else{
        if(this[options.hash.not]){
          if(this[options.hash.not] !== value){
            return options.fn(this);
          }
        }
        return options.inverse(this);
      }
    }

  });



};

/* -- BaldrickJS  V2.3 | (C) David Cramer - 2013 | MIT License */
(function($){
  // try not load again
  if( baldrickCache ){
    return;
  }
  
  $.fn.formJSON = function(){
    var form = $(this);

    var fields       = form.find('[name]'),
        json         = {},
        arraynames   = {};
    for( var v = 0; v < fields.length; v++){
      var field     = $( fields[v] ),
        name    = field.prop('name').replace(/\]/gi,'').split('['),
        value     = field.val(),
        lineconf  = {};

        if( field.is(':radio') || field.is(':checkbox') ){
          if( !field.is(':checked') ){
            continue;
          }
        }

      for(var i = name.length-1; i >= 0; i--){
        var nestname = name[i];
        if( typeof nestname === 'undefined' ){
          nestname = '';
        }
        if(nestname.length === 0){
          lineconf = [];
          if( typeof arraynames[name[i-1]] === 'undefined'){
            arraynames[name[i-1]] = 0;
          }else{
            arraynames[name[i-1]] += 1;
          }
          nestname = arraynames[name[i-1]];
        }
        if(i === name.length-1){
          if( value ){
            if( value === 'true' ){
              value = true;
            }else if( value === 'false' ){
              value = false;
            }else if( !isNaN( parseFloat( value ) ) && parseFloat( value ).toString() === value ){
              value = parseFloat( value );
            }else if( typeof value === 'string' && ( value.substr(0,1) === '{' || value.substr(0,1) === '[' ) ){
              try {
                value = JSON.parse( value );

              } catch (e) {
                //console.log( e );
              }
            }else if( typeof value === 'object' && value.length && field.is('select') ){
              var new_val = {};
              for( var i = 0; i < value.length; i++ ){
                new_val[ 'n' + i ] = value[ i ];
              }

              value = new_val;
            }
          }
          lineconf[nestname] = value;
        }else{
          var newobj = lineconf;
          lineconf = {};
          lineconf[nestname] = newobj;
        }   
      }
      $.extend(true, json, lineconf);
    };

    return json;
  }

  var baldrickCache     = {},
    baldrickRequests  = {},
    baldrickhelpers   = {
    _plugins    : {},
    load      : {},
    bind      : {},
    event     : function(el,e){
      return el;
    },
    pre_filter      : function(opts){
      return opts.data;
    },
    filter      : function(opts){
      return opts;
    },
    target      : function(opts){
      if(typeof opts.params.success === 'string'){
        if(typeof window[opts.params.success] === 'function'){
          window[opts.params.success](opts);
        }
      }else if(typeof opts.params.success === 'function'){
        opts.params.success(opts);
      }

      if(opts.params.target){

        if(opts.params.target.is('textarea,input') && typeof opts.data === 'object'){
          opts.params.target.val( JSON.stringify(opts.data) ).trigger('change');
        }else{
          opts.params.target[opts.params.targetInsert](opts.data);
        }
        if(typeof opts.params.callback === 'string'){
          if(typeof window[opts.params.callback] === 'function'){
            return window[opts.params.callback](opts);
          }
        }else if(typeof opts.params.callback === 'function'){
          return opts.params.callback(opts);
        }
      }
    },
    request_data : function(obj){
      return obj.data;
    },
    request     : function(opts){

      if( ( opts.params.trigger.data('cacheLocal') || opts.params.trigger.data('cacheSession') ) && !opts.params.trigger.data('cachePurge') ){

        var key;

        if( opts.params.trigger.data('cacheLocal') ){
          key = opts.params.trigger.data('cacheLocal');
        }else if(opts.params.trigger.data('cacheSession')){
          key = opts.params.trigger.data('cacheSession');
        }

        // check for a recent object
        if(typeof baldrickCache[key] !== 'undefined'){
          return {data: baldrickCache[key]};
        }       

        // check if there is a stored obejct to be loaded
        if(typeof(Storage)!=="undefined"){

          var cache;
          
          // load storage
          if( opts.params.trigger.data('cacheLocal') ){
            cache = localStorage.getItem( key );
          }else if(opts.params.trigger.data('cacheSession')){
            cache = sessionStorage.getItem( key );
          }
          if(cache){
            try {
              //baldrickCache[key] = JSON.parse(cache);
              cache = JSON.parse(cache);
            } catch (e) {
              //baldrickCache[key] = cache;
              cache = cache;
            }
            return {data: cache};
          }
          
        }

      }
      if( baldrickRequests[opts.params.trigger.prop('id')] ){
        baldrickRequests[opts.params.trigger.prop('id')].abort();
      }
      baldrickRequests[opts.params.trigger.prop('id')] = $.ajax(opts.request);
      return baldrickRequests[opts.params.trigger.prop('id')];
    },
    request_complete: function(opts){
      opts.params.complete(opts);
      opts.params.loadElement.removeClass(opts.params.loadClass);
      if( baldrickRequests[opts.params.trigger.prop('id')] ){
        delete baldrickRequests[opts.params.trigger.prop('id')];
      }
    },
    request_error : function(opts){
      opts.params.error(opts);
      opts.params.complete(opts.jqxhr,opts.textStatus);
    },
    refresh : function(opts, defaults){
      $(defaults.triggerClass).baldrick(defaults);
    }
  };

  $.fn.baldrick = function(opts){

    var do_helper = function(h, input, ev){
      var out;
      // pull in plugins before
      for(var before in defaults.helpers._plugins){
        if(typeof defaults.helpers._plugins[before][h] === 'function'){
          out = defaults.helpers._plugins[before][h](input, defaults, ev);
          if(typeof out !== 'undefined'){ input = out;}
          if(input === false){return false;}
        }
      }
      if(typeof defaults.helpers[h] === 'function'){
        out = defaults.helpers[h](input, defaults, ev);
        if(typeof out !== 'undefined'){ input = out;}
        if(!input){return false;}
      }
      // pull in plugins after
      for(var after in defaults.helpers._plugins){
        if(typeof defaults.helpers._plugins[after]['after_' + h] === 'function'){
          out = defaults.helpers._plugins[after]['after_' + h](input, defaults, ev);
          if(typeof out !== 'undefined'){ input = out;}
          if(input === false){return false;}
        }
      }
      return input;
    },
    serialize_form  = function(form){

      var config      = {},
        data_fields   = form.find('input,radio,checkbox,select,textarea,file'),
        objects     = [],
        arraynames    = {};

      // no fields - exit     
      if(!data_fields.length){
        return;
      }

      for( var v = 0; v < data_fields.length; v++){
        if( data_fields[v].getAttribute('name') === null){
          continue;
        }
        var field     = $(data_fields[v]),
          basename  = field.prop('name').replace(/\[/gi,':').replace(/\]/gi,''),//.split('[' + id + ']')[1].substr(1),
          name    = basename.split(':'),
          value     = ( field.is(':checkbox,:radio') ? field.filter(':checked').val() : field.val() ),
          lineconf  = {};         

        for(var i = name.length-1; i >= 0; i--){
          var nestname = name[i];
          if(nestname.length === 0){
            if( typeof arraynames[name[i-1]] === 'undefined'){
              arraynames[name[i-1]] = 0;
            }else{
              arraynames[name[i-1]] += 1;
            }
            nestname = arraynames[name[i-1]];
          }
          if(i === name.length-1){
            lineconf[nestname] = value;
          }else{
            var newobj = lineconf;
            lineconf = {};
            lineconf[nestname] = newobj;
          }   
        }
        
        $.extend(true, config, lineconf);
      };
      // give json object to trigger
      //params.data = JSON.stringify(config);
      //params.data = config;
      return config;
    },
    triggerClass  = this.selector,
    inst      = this.not('._tisBound');

    inst.addClass('_tisBound');
    if(typeof opts !== 'undefined'){
      if(typeof opts.helper === 'object'){
        baldrickhelpers._plugins._params_helpers_ = opts.helper;
      }
    }
    var defaults    = $.extend(true, opts, { helpers : baldrickhelpers}, {triggerClass:triggerClass}),
      ncb       = function(){return true;},
      callbacks   = {
        "before"  : ncb,
        "callback"  : false,
        "success" : false,
        "complete"  : ncb,
        "error"   : ncb
      },
      output;

    for(var c in callbacks){
      if(typeof defaults[c] === 'string'){
        callbacks[c] = (typeof window[defaults[c]] === 'function' ? window[defaults[c]] : ncb);
      }else if(typeof defaults[c] === 'function'){
        callbacks[c] = defaults[c];
      }
    }

    inst = do_helper('bind', inst);
    if(inst === false){return this;}
    return do_helper('ready', inst.each(function(key){
      if(!this.id){
        this.id = "baldrick_trigger_" + (new Date().getTime() + key);
      }
      var el = $(this), ev = (el.data('event') ? el.data('event') : (defaults.event ? defaults.event : ( el.is('form') ? 'submit' : 'click' )));
      el.on(ev, function(e){

        var tr = $(do_helper('event', this, e));

        if(tr.data('for')){
          var fort    = $(tr.data('for')),
            datamerge = $.extend({}, fort.data(), tr.data());
            delete datamerge['for'];
          fort.data(datamerge);
          if( fort.is('form') ){            
            fort.submit();
            return this;
          }else{
            return fort.trigger((fort.data('event') ? fort.data('event') : ev));
          }
        }
        if(tr.is('form') && !tr.data('request') && tr.attr('action')){
          tr.data('request', tr.attr('action'));
        }
        if(tr.is('a') && !tr.data('request') && tr.attr('href')){
          if(tr.attr('href').indexOf('#') < 0){
            tr.data('request', tr.attr('href'));
          }else{
            tr.data('href', tr.attr('href'));
          }
        }

        if((tr.data('before') ? (typeof window[tr.data('before')] === 'function' ? window[tr.data('before')](this, e) : callbacks.before(this, e)) : callbacks.before(this, e)) === false){return;}

        var params = {
          trigger: tr,
          callback : (tr.data('callback')   ? ((typeof window[tr.data('callback')] === 'function') ? window[tr.data('callback')] : tr.data('callback')) : callbacks.callback),
          success : (tr.data('success')   ? ((typeof window[tr.data('success')] === 'function') ? window[tr.data('success')] : tr.data('success')) : callbacks.success),
          method : (tr.data('method')     ? tr.data('method')       : (tr.attr('method')    ? tr.attr('method') :(defaults.method ? defaults.method : 'GET'))),
          dataType : (tr.data('type')     ? tr.data('type')       : (defaults.dataType    ? defaults.dataType : false)),
          timeout : (tr.data('timeout')   ? tr.data('timeout')      : 120000),
          target : (tr.data('target')     ? ( tr.data('target') === '_parent' ? tr.parent() : ( tr.data('target') === '_self' ? $(tr) : $(tr.data('target')) ) )      : (defaults.target      ? $(defaults.target) : $('<html>'))),
          targetInsert : (tr.data('targetInsert') ? (tr.data('targetInsert') === 'replace' ? 'replaceWith' : tr.data('targetInsert')) : (defaults.targetInsert ? (defaults.targetInsert === 'replace' ? 'replaceWith': defaults.targetInsert) : 'html')),
          loadClass : (tr.data('loadClass')   ? tr.data('loadClass')      : (defaults.loadClass   ? defaults.loadClass : 'loading')),
          activeClass : (tr.data('activeClass') ? tr.data('activeClass')    : (defaults.activeClass   ? defaults.activeClass : 'active')),
          activeElement : (tr.data('activeElement') ? (tr.data('activeElement') === '_parent' ? tr.parent() :$(tr.data('activeElement'))) : (defaults.activeElement ? (defaults.activeElement === '_parent' ? tr.parent() : $(defaults.activeElement)) : tr)),
          cache : (tr.data('cache')     ? tr.data('cache')        : (defaults.cache     ? defaults.cache : false)),
          complete : (tr.data('complete')   ? (typeof window[tr.data('complete')] === 'function'    ? window[tr.data('complete')] : callbacks.complete ) : callbacks.complete),
          error : (tr.data('error')   ? (typeof window[tr.data('error')] === 'function'   ? window[tr.data('error')] : callbacks.error ) : callbacks.error),
          resultSelector : false,
          event : ev
        };
        params.url      = (tr.data('request')   ? ( tr.data('request') )      : (defaults.request     ? defaults.request : params.callback));
        params.loadElement  = (tr.data('loadElement') ? (tr.data('loadElement') === '_parent' ? tr.parent() :$(tr.data('loadElement')))   : (defaults.loadElement   ? ($(defaults.loadElement) ? $(defaults.loadElement) : params.target) : params.target));

        params = do_helper('params', params);
        if(params === false){return false;}

        // check if request is a function
        e.preventDefault();
        if(typeof window[params.url] === 'function'){
          
          var dt = window[params.url](params, ev);
          dt = do_helper('pre_filter', {data:dt, params: params});
          dt = do_helper('filter', {data:dt, rawData: dt, params: params});
          do_helper('target', dt);
          do_helper('refresh', {params:params});
          do_helper('request_complete', {jqxhr:null, textStatus:'complete', request:request, params:params});

          return this;
        }else{

          try{
            if( $(params.url).length ){
              var dt = $(params.url).is('input,select,radio,checkbox,file,textarea') ? $(params.url).val() : ( $(params.url).is('form') ? serialize_form( $(params.url) ) : $(params.url).html() );
            }
          }catch (e){}

          if(typeof dt !== 'undefined'){

            if(params.dataType === 'json'){
              try{
                dt = JSON.parse(dt);
              }catch (e){}
            }

            dt = do_helper('pre_filter', {data:dt, params: params});
            dt = do_helper('filter', {data:dt, rawData: dt, params: params});
            do_helper('target', dt);
            do_helper('refresh', {params:params});
            do_helper('request_complete', {jqxhr:null, textStatus:'complete', request:request, params:params});

            var dt_enabled = true;            
            return this;
          }
        }
        switch (typeof params.url){
          case 'function' : return params.url(this, e);
          case 'boolean' :
          case 'object': return;
          case 'string' :
            if(params.url.indexOf(' ') > -1){
              var rp = params.url.split(' ');
              params.url  = rp[0];
              params.resultSelector = rp[1];
            }
        }
        
        var active = (tr.data('group') ? $('._tisBound[data-group="'+tr.data('group')+'"]').each(function(){
          var or  = $(this),
            tel = (or.data('activeElement') ? (or.data('activeElement') === '_parent' ? or.parent() :$(or.data('activeElement'))) : (defaults.activeElement ? (defaults.activeElement === '_parent' ? tr.parent() : $(defaults.activeElement)) : or) );
          tel.removeClass((or.data('activeClass') ? or.data('activeClass') : (defaults.activeClass ? defaults.activeClass : params.activeClass)));}
        ) : $('._tisBound:not([data-group])').each(function(){
          var or  = $(this),
            tel = (or.data('activeElement') ? (or.data('activeElement') === '_parent' ? or.parent() :$(or.data('activeElement'))) : (defaults.activeElement ? (defaults.activeElement === '_parent' ? tr.parent() : $(defaults.activeElement)) : or) );
          tel.removeClass((or.data('activeClass') ? or.data('activeClass') : (defaults.activeClass ? defaults.activeClass : params.activeClass)));}
        ));
        
        params.activeElement.addClass(params.activeClass);
        params.loadElement.addClass(params.loadClass);
        var data;
        if(FormData && ( tr.is('input:file') || params.method === 'POST') ){

          params.method   = 'POST';
          params.contentType  = false;
          params.processData  = false;
          params.cache    = false;
          params.xhrFields  = {
            onprogress: function (e) {
              if (e.lengthComputable) {
                //console.log('Loaded '+ (e.loaded / e.total * 100) + '%');
              } else {
                //console.log('Length not computable.');
              }
            }
          };

          if(tr.is('form')){
            data = new FormData(tr[0]);
          }else{

            data = new FormData();
          }

          if(tr.is('input,select,textarea')){
            // add value as _value for each access
            tr.data('_value', tr.val());
          }
          // make field vars
          for(var att in params.trigger.data()){
            data.append(att, params.trigger.data(att));
          }
          // convert param.data to json
          if(params.data){
            data.append('data', JSON.stringify(params.data));
          }
          // use input
          if(tr.is('input,select,textarea')){

            if(tr.is('input:file')){                            
              if(tr[0].files.length > 1){               
                for( var file = 0; file < tr[0].files.length; file++){
                  data.append(tr.prop('name'), tr[0].files[file]);
                }
              }else{
                data.append(tr.prop('name'), tr[0].files[0]);
              }

            }else if(tr.is('input:checkbox') || tr.is('input:radio')){
              if(tr.prop('checked')){
                data.append(tr.prop('name'), tr.val());
              }
            }else{
              data.append(tr.prop('name'), tr.val());
            }
          }
        }else{
          
          var sd = tr.serializeArray(), atts = params.trigger.data(), param = [];
          //console.log(atts);
          // insert user set params
          if(defaults.data){
            atts = $.extend(defaults.data, atts);
          }

          if(sd.length){
            $.each( sd, function(k,v) {
              param.push(v);
            });
            params.requestData = serialize_form(tr);
          }
          // convert param.data to json
          if(params.data){
            atts = $.extend(atts, params.data);
          }         
          data = atts;
          params.requestData = $.extend(tr.data(), params.requestData);
        }

        var request = {
            url   : params.url,
            data  : do_helper('request_data', {data:data, params: params }),
            cache : params.cache,
            timeout : params.timeout,
            type  : params.method,
            success : function(dt, ts, xhr){
              if(params.resultSelector){
                if(typeof dt === 'object'){
                  var traverse = params.resultSelector.replace(/\[/g,'.').replace(/\]/g,'').split('.'),
                    data_object = dt;
                  for(var i=0; i<traverse.length; i++){
                    data_object = data_object[traverse[i]];
                  }
                  dt = data_object;
                }else if (typeof dt === 'string'){
                  var tmp = $(params.resultSelector, $('<html>').html(dt));
                  if(tmp.length === 1){
                    dt = $('<html>').html(tmp).html();
                  }else{
                    dt = $('<html>');
                    tmp.each(function(){
                      dt.append(this);
                    });
                    dt = dt.html();
                  }
                }
              }
              var rawdata = dt;             
              if(params.trigger.data('cacheLocal') || params.trigger.data('cacheSession')){

                
                var key;

                if( params.trigger.data('cacheLocal') ){
                  key = params.trigger.data('cacheLocal');
                }else if(params.trigger.data('cacheSession')){
                  key = params.trigger.data('cacheSession');
                }

                // add to local storage for later
                if(typeof(Storage)!=="undefined"){
                  if( params.trigger.data('cacheLocal') ){
                    try{
                      localStorage.setItem( key, xhr.responseText );
                    } catch (e) {
                      console.log(e);
                    }
                  }else if( params.trigger.data('cacheSession') ){
                    try{
                      sessionStorage.setItem( key, xhr.responseText );
                    } catch (e) {
                      console.log(e);
                    }

                  }
                }

                // add to current cache object
                //baldrickCache[key] = dt;
                $(window).trigger('baldrick.cache', key);
              }

              dt = do_helper('pre_filter', {data:dt, request: request, params: params, xhr: xhr});
              dt = do_helper('filter', {data:dt, rawData: rawdata, request: request, params: params, xhr: xhr});
              do_helper('target', dt);
            },
            complete: function(xhr,ts){
              
              do_helper('request_complete', {jqxhr:xhr, textStatus:ts, request:request, params:params});
              
              do_helper('refresh', {jqxhr:xhr, textStatus:ts, request:request, params:params});

              if(tr.data('once')){
                tr.off(ev).removeClass('_tisBound');
              }
            },
            error: function(xhr,ts,ex){
              do_helper('request_error', {jqxhr:xhr, textStatus:ts, error:ex, request:request, params:params});
            }
          };
        if(params.dataType){
          request.dataType = params.dataType;
        }
        if(typeof params.contentType !== 'undefined'){
          request.contentType = params.contentType;
        }
        if(typeof params.processData !== 'undefined'){
          request.processData = params.processData;
        }
        if(typeof params.xhrFields !== 'undefined'){
          request.xhrFields = params.xhrFields;
        }

        request = do_helper('request_params', request, params);
        if(request === false){return inst;}

        var request_result = do_helper('request', {request: request, params: params});

        // A Request helper returns a completed object, if it contains data, push to the rest.
        if(request_result.data){

          var dt    = request_result.data,
            rawdata = dt;

          do_helper('target'        ,
              do_helper('filter'    ,
              do_helper('pre_filter'  , {data:dt, request: request, params: params})
            )
          );
          do_helper('request_complete', {jqxhr:false, textStatus:true, request:request, params:params});
          do_helper('refresh'     , {jqxhr:false, textStatus:true, request:request, params:params});


        }
      });
      if(el.data('autoload') || el.data('poll')){
        if(el.data('delay')){
          setTimeout(function(el, ev){
            return el.trigger(ev);
          }, el.data('delay'), el, ev);
        }else{
          el.trigger(ev);
        }
      }

      if(el.data('poll')){
        if(el.data('delay')){
          setTimeout(function(el, ev){
            return setInterval(function(el, ev){
              return el.trigger(ev);
            }, el.data('poll'), el, ev);
          }, el.data('delay'));
        }else{
          setInterval(function(el, ev){
            return el.trigger(ev);
          }, el.data('poll'), el, ev);
        }
      }
      return this;
    }));
  };
  $.fn.baldrick.cacheObject = function(id, object){
    baldrickCache[id] = object;
  };
  $.fn.baldrick.registerhelper = function(slug, helper, callback){
    var newhelper = {};
    if(typeof helper === 'object'){
      newhelper[slug] = helper;
      baldrickhelpers._plugins = $.extend(true, newhelper, baldrickhelpers._plugins);
    }else if(typeof helper === 'string' && typeof slug === 'string' && typeof callback === 'function'){
      newhelper[helper] = {};
      newhelper[helper][slug] = callback;
      baldrickhelpers._plugins = $.extend(true, newhelper, baldrickhelpers._plugins);
    }
    
  };

})(jQuery);

/* Baldrick handlebars.js templating plugin */
(function($){
  var compiledTemplates = {};
  $.fn.baldrick.registerhelper('handlebars', {
    bind  : function(triggers, defaults){
      var templates = triggers.filter("[data-template-url]");
      if(templates.length){
        templates.each(function(){
          var trigger = $(this);
          //console.log(trigger.data());
          if(typeof compiledTemplates[trigger.data('templateUrl')] === 'undefined'){
            compiledTemplates[trigger.data('templateUrl')] = true;

            if(typeof(Storage)!=="undefined"){

              var cache, key;
              
              if(trigger.data('cacheLocal')){
                
                key = trigger.data('cacheLocal');
                
                cache = localStorage.getItem( 'handlebars_' + key );
              
              }else if(trigger.data('cacheSession')){

                key = trigger.data('cacheSession');

                cache = sessionStorage.getItem( 'handlebars_' + key );
              }

            }
            
            if(cache){
              compiledTemplates[trigger.data('templateUrl')] = Handlebars.compile(cache);
            }else{
              $.get(trigger.data('templateUrl'), function(data, ts, xhr){
                
                if(typeof(Storage)!=="undefined"){

                  var key;
                  
                  if(trigger.data('cacheLocal')){
                    
                    key = trigger.data('cacheLocal');

                    localStorage.setItem( 'handlebars_' + key, xhr.responseText );
                  
                  }else if(trigger.data('cacheSession')){
                    
                    key = trigger.data('cacheSession');

                    sessionStorage.setItem( 'handlebars_' + key, xhr.responseText );
                  }
                }

                compiledTemplates[trigger.data('templateUrl')] = Handlebars.compile(xhr.responseText);
              });
            }
          }
        });
      }

    },
    request_params  : function(request, defaults, params){
      if((params.trigger.data('templateUrl') || params.trigger.data('template')) && typeof Handlebars === 'object'){
        request.dataType = 'json';
        return request;
      }
    },
    filter      : function(opts, defaults){     
      
      if(opts.params.trigger.data('templateUrl')){        
        if( typeof compiledTemplates[opts.params.trigger.data('templateUrl')] === 'function' ){         
          opts.data = compiledTemplates[opts.params.trigger.data('templateUrl')](opts.data);          
        }
      }else if(opts.params.trigger.data('template')){
        if( typeof compiledTemplates[opts.params.trigger.data('template')] === 'function' ){
          opts.data = compiledTemplates[opts.params.trigger.data('template')](opts.data);
        }else{
          if($(opts.params.trigger.data('template'))){
            compiledTemplates[opts.params.trigger.data('template')] = Handlebars.compile($(opts.params.trigger.data('template')).html());
            opts.data = compiledTemplates[opts.params.trigger.data('template')](opts.data);
          }
        }
      }

      return opts;
    }
  });

})(jQuery);


/* Baldrick modals.js templating plugin */
(function($){

  var wm_hasModal = false;
  
  $.fn.baldrick.registerhelper('baldrick_modal', {
    close_modal: function(modal, modalBackdrop, trigger, size_cycle, ev){
      $('body').css('overflow', '');
      ev.preventDefault();
      modalBackdrop.fadeOut(200);
      modal.fadeOut(200, function(){
        modal.remove();
        modalBackdrop.remove();
        if( size_cycle ){
          clearTimeout( size_cycle );
        }
        trigger.removeClass( ( trigger.data('activeClass') ? trigger.data('activeClass') : 'active' ) );
        $('.baldrick-modal-wrap').css('zIndex' , '');
      });
    },
    resize_modal: function(modal, trigger){
      
      $('body').css('overflow', 'hidden');

      var windowWidth = $(window).width(),
        windowHeight = $(window).height(),
        modalHeight = ( modal.data('height') ? modal.data('height') : ( trigger.data('modalHeight') ? trigger.data('modalHeight') : 350 ) ),
        modalWidth = ( modal.data('width') ? modal.data('width') : ( trigger.data('modalWidth') ? trigger.data('modalWidth') : 450 ) ),
        body = modal.find('.baldrick-modal-body')
        footer = modal.find('.baldrick-modal-footer'),
        title = modal.find('.baldrick-modal-title'),
        current_size = '';

        if( modalHeight === 'auto' ){
          body.css( 'position', 'initial' );
          modalHeight = body.outerHeight() + footer.outerHeight() + title.outerHeight();
          body.css( 'position', '' );
        }
        if( modalWidth === 'auto' ){
          modalWidth = 450;
        }

        if( windowWidth <= 700 && windowWidth > 600 ){
          modalHeight = windowHeight - 30;
          modalWidth = windowWidth - 30;
        }else if( windowWidth <= 600 ){
          modalHeight = windowHeight;
          modalWidth = windowWidth;
        }

        var modalTop = ( windowHeight / 2) - ( modalHeight / 2 ),
            modalLeft  = ( windowWidth / 2) - ( modalWidth / 2 );

        current_size = {
          top: ( modalHeight < windowHeight ? ( modalTop / 3 * 2 ) : 0 ),
          left: ( modalWidth < windowWidth ? modalLeft : 0 ),
          width: ( modalWidth < windowWidth ? modalWidth : windowWidth ),
          height: ( modalHeight < windowHeight ? modalHeight : windowHeight )
        };

        modal.css( current_size );

    },
    refresh: function(obj){
      if(obj.params.trigger.data('modalAutoclose')){
        $('#' + obj.params.trigger.data('modalAutoclose') + '_baldrickModalCloser').trigger('click');
      }
      // inject tabs      
      var navtabs = obj.params.target.find('[data-tab]');
      if( navtabs.length ){
        var tabs = $('<ul>', { "class" : "navtabs" } ),
            hasSelection = false;
       
        navtabs.each( function(k,v){
          var tab = $(this),
              element = $('<li>'),
              link = $('<a>', { "href": '#'});

            tab.addClass('hidden');
            link.html( tab.data('tab') ).attr('title', tab.data('tab') );
            element.append( link );
            if( tab.data('selected') && false === hasSelection ){
              element.addClass('selected');
              hasSelection = true;
              tab.show();
            }
          link.on('click', function(e){
            e.preventDefault();
            var clicked = $(this);
            navtabs.hide();
            tab.show();
            tabs.children().removeClass('selected');
            clicked.parent().addClass('selected');
            $(window).trigger('modaltabchange');
          });
          tabs.append(element);

        } );

        if( false === hasSelection ){
          tabs.children().first().addClass('selected');
          navtabs.first().show();          
        }

        tabs.css('top', obj.params.target.parent().find('.baldrick-modal-title').outerHeight() );
        if( obj.params.trigger.data('modalButtons' ) ){
          tabs.css('bottom', obj.params.target.parent().find('.baldrick-modal-footer').outerHeight() );
        }else{
          tabs.css('bottom', 0 );
        }
        

        obj.params.target.before( tabs ).addClass('has-tabs');

      }            
    },
    event : function(el, obj){
      var trigger = $(el), modal_id = 'wm';     
      if(trigger.data('modal') && wm_hasModal === false){
        if(trigger.data('modal') !== 'true'){
          modal_id = trigger.data('modal');
        }
        if(!$('#' + modal_id + '_baldrickModal').length){
          $('.baldrick-modal-wrap').css('zIndex' , '100099');
          //wm_hasModal = true;
          // write out a template wrapper.
          var modal = $('<form>', {
              id          : modal_id + '_baldrickModal',
              tabIndex      : -1,
              "ariaLabelled-by" : modal_id + '_baldrickModalLable',
              "class"       : "baldrick-modal-wrap ",
              "method"    : "POST"
            }),         
          //modalDialog = $('<div>', {"class" : "modal-dialog"});
          //modalBackdrop = $('.baldrick-backdrop').length ? $('.baldrick-backdrop') : $('<div>', {"class" : "baldrick-backdrop"});
          modalBackdrop = $('.baldrick-backdrop').length ? $('<div>', {"class" : "baldrick-backdrop-invisible"}) : $('<div>', {"class" : "baldrick-backdrop"});
          modalContent = $('<div>', {"class" : "baldrick-modal-body",id: modal_id + '_baldrickModalBody'});
          modalFooter = $('<div>', {"class" : "baldrick-modal-footer",id: modal_id + '_baldrickModalFooter'});
          modalHeader = $('<div>', {"class" : "baldrick-modal-title", id : modal_id + '_baldrickModalTitle'});
          modalCloser = $('<a>', { "href" : "#close", "class":"baldrick-modal-closer", "data-dismiss":"modal", "aria-hidden":"true",id: modal_id + '_baldrickModalCloser'}).html('&times;');
          modalTitle = $('<h3>', {"class" : "modal-label", id : modal_id + '_baldrickModalLable'});
          
          modalHeader.append(modalCloser).appendTo(modal);

          if(trigger.data('modalTitle')){
            modalHeader.append(modalTitle); 
          }else{
            modalHeader.height(0).hide();
          }
          if(!trigger.data('modalButtons')){
            modalFooter.height(0).hide();
          }
          if(trigger.data('modalClass')){
            modal.addClass(trigger.data('modalClass'));
          }

          var resize_modal = this.resize_modal,size_cycle = null,
            //size_cycle = setInterval(function(){
            //  resize_modal( modal, trigger );
            //}, 400),

            // RESET SIZE
              resize_modal = this.resize_modal,
              resize_action = function(){
                 resize_modal( modal, trigger );
              },
            close_modal = this.close_modal,
            modal_closer = function(e){
              if(e.type === 'keypress'){
                if(e.keyCode !== 27){                 
                  return;
                }                
              }
              $(window).off('keypress', modal_closer );
              $(window).off('resize', resize_action);
              close_modal(modal, modalBackdrop, trigger, size_cycle, e);
            };
          
          $(window).on('resize', resize_action);

          modalBackdrop.on('click', modal_closer );
          modalCloser.on('click', modal_closer );
          $(window).on('keypress', modal_closer )
          
          modal.on('keyup', 'select,input,checkbox,radio,textarea', function(){
            $(window).off('keypress', modal_closer );
          })

          modalContent.appendTo(modal);
          modalFooter.appendTo(modal);

          modal.appendTo($('body')).hide().fadeIn(200);
          if( trigger.data('modalFloat') ){
            modalBackdrop.css({'background': 'transparent'});
          }
          modalBackdrop.insertBefore(modal).hide().fadeIn(200);
          

        }
      }
    },
    request_complete  : function(obj, params){
      if(obj.params.trigger.data('modal')){
        var modal_id = 'wm',loadClass = 'spinner', modal, modalBody;
        if(obj.params.trigger.data('modal') !== 'true'){
          modal_id = obj.params.trigger.data('modal');
        }

        modal       = $('#' + modal_id + '_baldrickModal');
        modalBody   = $('#' + modal_id + '_baldrickModalBody');
        modalTitle  = $('#' + modal_id + '_baldrickModalTitle');
        modalButtons= $('#' + modal_id + '_baldrickModalFooter button');
        modalButtons.prop('disabled', false);

        if(obj.params.trigger.data('loadClass')){
          loadClass = obj.params.trigger.data('loadClass');
        }

        if(obj.params.trigger.data('modalLife')){
          var delay = parseFloat(obj.params.trigger.data('modalLife'));
          if(delay > 0){
            setTimeout(function(){
              $('#' + modal_id + '_baldrickModalCloser').trigger('click');
            }, delay);
          }else{
            $('#' + modal_id + '_baldrickModalCloser').trigger('click');
          }
        }
        //$('#' + modal_id + '_baldrickModalLoader').hide();
        modalBody.removeClass(loadClass).show();

        //if(obj.params.trigger.data('modalCenter')){
          modal = this.resize_modal( modal, obj.params.trigger );
        //}
        
      }
    },
    after_filter  : function(obj){
      if(obj.params.trigger.data('modal')){
        if(obj.params.trigger.data('targetInsert')){
          var modal_id = 'wm';
          if(obj.params.trigger.data('modal') !== 'true'){
            modal_id = obj.params.trigger.data('modal');
          }
          var data = $(obj.data).prop('id', modal_id + '_baldrickModalBody');
          obj.data = data;
        }
      }
      return obj;
    },
    params  : function(params,defaults){

      var trigger = params.trigger, modal_id = 'wm', loadClass = 'spinner';
      if(params.trigger.data('modal') !== 'true'){
        modal_id = params.trigger.data('modal');
      }
      if(params.trigger.data('loadClass')){
        loadClass = params.trigger.data('loadClass');
      }

      if(trigger.data('modal') && (params.url || trigger.data('modalContent'))){
        var modal;

        if(params.url){
          params.target = $('#' + modal_id + '_baldrickModalBody');
          params.loadElement = $('#' + modal_id + '_baldrickModalLoader');
          params.target.empty();
        }

        if(trigger.data('modalTemplate')){
          modal = $(trigger.data('modalTemplate'));
        }else{
          modal = $('#' + modal_id + '_baldrickModal');
        }
        // close if already open
        if($('.modal-backdrop').length){
          //modal.modal('hide');
        }

        // get options.
        var label = $('#' + modal_id + '_baldrickModalLable'),
          //loader  = $('#' + modal_id + '_baldrickModalLoader'),
          title  = $('#' + modal_id + '_baldrickModalTitle'),
          body  = $('#' + modal_id + '_baldrickModalBody'),
          footer  = $('#' + modal_id + '_baldrickModalFooter');

        // reset modal
        //modal.removeClass('fade');

        label.empty().parent().hide();
        body.addClass(loadClass);

        footer.empty().hide();
        if(trigger.data('modalTitle')){
          label.html(trigger.data('modalTitle')).parent().show();
        }
        if(trigger.data('modalButtons')){
          var buttons = $.trim(trigger.data('modalButtons')).split(';'),
            button_list = [];

          body.addClass('has-buttons');

          if(buttons.length){
            footer.height('auto');
            for(b=0; b<buttons.length;b++){
              var options   = buttons[b].split('|'),
                buttonLabel = options[0],
                callback  = options[1].trim(),
                atts    = $.extend({}, {"type": "button", "class":'button '}, ( callback.substr(0,1) === '{' ? jQuery.parseJSON(callback) : {"data-callback" : callback} ) ),
                button    = $('<button>', atts);
              if(options[2]){
                button.addClass(options[2]);
              }
              if(atts['data-modal-close']){
                button.data('callback', function(){
                  $('#' + modal_id + '_baldrickModalCloser').trigger('click');
                });
              }
              if(callback === 'dismiss'){
                button.on('click', function(){
                  $('#' + modal_id + '_baldrickModalCloser').trigger('click');
                })
              }else{
                button.addClass(defaults.triggerClass.substr(1));
              }
              button.prop('disabled', true);
              
              footer.append(button.html(buttonLabel));
              if(b<buttons.length){
                footer.append('&nbsp;');
              }
            }
            footer.show();
          }

        }



        //optional content
        if(trigger.data('modalContent')){
          body.html($(trigger.data('modalContent')).html());
          loader.hide();
          body.show();
          $(defaults.triggerClass).baldrick(defaults);
        }

        $(window).trigger('resize');
        // launch
        /*modal.modal('show').on('hidden.bs.modal', function (e) {
          wm_hasModal = false;
          $(this).remove();
        });*/
      }
    }
  });

})(jQuery);