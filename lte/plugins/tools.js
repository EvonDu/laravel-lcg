export default {
    install(app, options){
        app.config.globalProperties.$tools = {
            //获取请求串
            getQueryString(params) {
                //匿名函数用于处理递归
                let recursion = function(variable, data){
                    let result = "";
                    for(let key in data){
                        let name = variable ? (variable + "[" + key + "]" ) : key;
                        if(typeof data[key] === "object")
                            result += recursion(name, data[key]);
                        else
                            result += ("&" + name + "=" + data[key]);
                    }
                    return result;
                }
                //转化为请求串
                let result = recursion(null, params);
                return result.slice(1);
            },
        }
    }
}
