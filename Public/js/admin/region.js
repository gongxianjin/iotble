/**
 * Created by sun on 2017/7/29.
 */

function getaddress(obj,type,target) {
    postData = {};
    var parent = obj.options[obj.selectedIndex].value;
    postData['parent'] = parent;
    postData['type']  =  type;
    postData['target']  =  target;
    var url = SCOPE.region_url;
    $.post(url, postData, function(result){
        if(result.status == 1) {
            var sel = document.getElementById(result.data.target);

            sel.length = 1;
            sel.selectedIndex = 0;
            sel.style.display = (result.data.regions.length == 0 ) ? "none" : '';

            if (document.all)
            {
                sel.fireEvent("onchange");
            }else
            {
                var evt = document.createEvent("HTMLEvents");
                evt.initEvent('change', true, true);
                sel.dispatchEvent(evt);
            }
            if (result.data.regions)
            {
                for (i = 0; i < result.data.regions.length; i ++ )
                {
                    var opt = document.createElement("OPTION");
                    opt.value = result.data.regions[i].region_id;
                    opt.text  = result.data.regions[i].region_name;
                    sel.options.add(opt);
                }
            }
        }
        if(result.status == 0) {
            // TODO
            return dialog.error(result.message);
        }
    },"json");

}
