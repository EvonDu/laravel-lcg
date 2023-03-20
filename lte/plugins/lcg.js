export default {
    install(app, options){
        app.provide('LCG', {
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
            //获取请求参数
            getSearchOptions(options_new, options_old){
                //字段参数
                let model = options_new?.model ? options_new?.model : options_old?.model;
                //分页参数
                let paginate = {
                    page: (options_new?.model) ? 1 : (options_new?.paginate?.page || options_old?.paginate?.page || 1),
                    size: options_new?.paginate?.size || options_old?.paginate?.size || 20,
                };
                //排序参数
                let sort = {};
                if(options_new?.sort){
                    sort = {
                        prop: options_new?.sort?.order ? options_new?.sort?.prop : "",
                        order: options_new?.sort?.order,
                    };
                }
                else if(options_old?.sort){
                    sort = {
                        prop: options_old?.sort?.order ? options_old?.sort?.prop : "",
                        order: options_old?.sort?.order,
                    };
                }

                //组合参数
                let params = {};
                //字段参数
                for (var key in model){
                    params[key] = model[key];
                }
                //分页参数
                if(paginate?.page > 1)
                    params["_page"] = paginate.page;
                if(paginate?.size !== 20)
                    params["_size"] = paginate.size;
                //排序参数
                if(sort.prop)
                    params["_sort"] = sort.prop;
                if(sort.order && sort.order === "descending")
                    params["_order"] = "desc"; //"asc";

                //返回结果
                return {
                    params: params,
                    model: model,
                    paginate: paginate,
                    sort: sort,
                }
            }
        })
    }
}
