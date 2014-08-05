this["MovieManager"] = this["MovieManager"] || {};
this["MovieManager"]["Templates"] = this["MovieManager"]["Templates"] || {};

this["MovieManager"]["Templates"]["list"] = Handlebars.template(function (Handlebars,depth0,helpers,partials,data) {
  this.compilerInfo = [4,'>= 1.0.0'];
helpers = this.merge(helpers, Handlebars.helpers); data = data || {};
  var buffer = "", stack1, functionType="function", escapeExpression=this.escapeExpression, self=this;

function program1(depth0,data) {
  
  var buffer = "", stack1;
  buffer += "\n        <table class=\"table table-hover movies\">\n            <thead>\n            <tr>\n                <th class=\"index\">#</th>\n                <th class=\"filename\">Filename</th>\n                <th class=\"chunks\">Chunks</th>\n                <th class=\"folder\">Folder</th>\n                <th class=\"link\">Info</th>\n                <th class=\"screenshot\">Screeny</th>\n                <th class=\"poster\">Poster</th>\n                <th class=\"format\">Format</th>\n                <th class=\"action\"></th>\n            </tr>\n            </thead>\n            <tbody>\n            ";
  stack1 = helpers.each.call(depth0, (depth0 && depth0.movies), {hash:{},inverse:self.noop,fn:self.program(2, program2, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\n            </tbody>\n        </table>\n    ";
  return buffer;
  }
function program2(depth0,data) {
  
  var buffer = "", stack1, helper;
  buffer += "\n                <tr class=\"movie\">\n                    <td class=\"index\">\n                        "
    + escapeExpression(((stack1 = (data == null || data === false ? data : data.index)),typeof stack1 === functionType ? stack1.apply(depth0) : stack1))
    + "\n                    </td>\n                    <td class=\"filename\">\n                        ";
  if (helper = helpers.name) { stack1 = helper.call(depth0, {hash:{},data:data}); }
  else { helper = (depth0 && depth0.name); stack1 = typeof helper === functionType ? helper.call(depth0, {hash:{},data:data}) : helper; }
  buffer += escapeExpression(stack1)
    + "\n                    </td>\n                    <td class=\"chunks\">\n                        ";
  stack1 = helpers.each.call(depth0, (depth0 && depth0.chunks), {hash:{},inverse:self.noop,fn:self.program(3, program3, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\n                    </td>\n                    <td class=\"folder\">\n                        ";
  stack1 = helpers['if'].call(depth0, (depth0 && depth0.folder), {hash:{},inverse:self.program(7, program7, data),fn:self.program(5, program5, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\n                    </td>\n                    <td class=\"link\">\n                        ";
  stack1 = helpers['if'].call(depth0, (depth0 && depth0.link), {hash:{},inverse:self.program(7, program7, data),fn:self.program(5, program5, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\n                    </td>\n                    <td class=\"screenshot\">\n                        ";
  stack1 = helpers['if'].call(depth0, (depth0 && depth0.screenshot), {hash:{},inverse:self.program(7, program7, data),fn:self.program(5, program5, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\n                    </td>\n                    <td class=\"poster\">\n                        ";
  stack1 = helpers['if'].call(depth0, (depth0 && depth0.poster), {hash:{},inverse:self.program(7, program7, data),fn:self.program(5, program5, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\n                    </td>\n                    <td class=\"format\">\n                        ";
  stack1 = helpers['if'].call(depth0, (depth0 && depth0.format), {hash:{},inverse:self.program(7, program7, data),fn:self.program(5, program5, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\n                    </td>\n                    <td class=\"action\">\n                        <button class=\"btn btn-xs btn-info go-imdb\" value=\"";
  if (helper = helpers.path) { stack1 = helper.call(depth0, {hash:{},data:data}); }
  else { helper = (depth0 && depth0.path); stack1 = typeof helper === functionType ? helper.call(depth0, {hash:{},data:data}) : helper; }
  buffer += escapeExpression(stack1)
    + "\">GO</button>\n                    </td>\n                </tr>\n            ";
  return buffer;
  }
function program3(depth0,data) {
  
  var buffer = "";
  buffer += "\n                            <button type=\"button\" class='btn btn-xs btn-default chunk'>\n                                "
    + escapeExpression((typeof depth0 === functionType ? depth0.apply(depth0) : depth0))
    + "\n                            </button>\n                        ";
  return buffer;
  }

function program5(depth0,data) {
  
  
  return "\n                            <span class=\"glyphicon glyphicon-ok\"></span>\n                        ";
  }

function program7(depth0,data) {
  
  
  return "\n                            <span class=\"glyphicon glyphicon-remove\"></span>\n                        ";
  }

function program9(depth0,data) {
  
  
  return "\n        <p class=\"text-warning center\">Please, choose a folder.</p>\n    ";
  }

  buffer += "<div class=\"col-md-12\">\n    ";
  stack1 = helpers['if'].call(depth0, (depth0 && depth0.movies), {hash:{},inverse:self.program(9, program9, data),fn:self.program(1, program1, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\n</div>";
  return buffer;
  });

this["MovieManager"]["Templates"]["suggestion"] = Handlebars.template(function (Handlebars,depth0,helpers,partials,data) {
  this.compilerInfo = [4,'>= 1.0.0'];
helpers = this.merge(helpers, Handlebars.helpers); data = data || {};
  var buffer = "", stack1, helper, functionType="function", escapeExpression=this.escapeExpression;


  buffer += "<tr class=\"suggestionsRow\">\n    <td colspan=\"9\">\n        <h2>IMDb Movie Search Results:</h2>\n        <ul class=\"suggestions center\"></ul>\n        <input type=\"hidden\" value=\"";
  if (helper = helpers.fullname) { stack1 = helper.call(depth0, {hash:{},data:data}); }
  else { helper = (depth0 && depth0.fullname); stack1 = typeof helper === functionType ? helper.call(depth0, {hash:{},data:data}) : helper; }
  buffer += escapeExpression(stack1)
    + "\" class=\"file\" />\n        <div class=\"center clearfix\">\n            <button class=\"btn btn-danger hide-suggestions\">Cancel</button>\n        </div>\n    </td>\n</tr>";
  return buffer;
  });