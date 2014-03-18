<table id="datagrid-categories">
</table>
<script type="text/javascript">
$(function(){
    var height = $('.indexcenter').height();
    $('#datagrid-categories').datagrid({
        url:'categories',
        idField:'id',
        pagination:true,
        rownumbers:false,
        fitColumns:true,
        checkbox:true,
        height:height-60,
        selectOnCheck:true,
        //singleSelect:true,
        toolbar:[{
            id:'btnadd',
            text:'New Category',
            iconCls:'icon-add',
            handler:function(){
            	var dialogCon = $('<div></div>');
                dialogCon.dialog({
                    href: 'newcategory',
                    width:600,
                    //height:400,
                    modal : true,
                    resizable:true,
                    collapsible:true,
                    maximizable:true,
                    title:'New Category',
                    buttons : [{
                        text : 'submit',
                        iconCls : 'icon-ok',
                        handler : function() {
                            var d = $(this).closest('.window-body');
                            $('#add-form-category').form('submit', {
                                url : 'newcategory',
                                success : function(result) {
                                    var r = $.parseJSON(result);
                                    if (r.success) {
                                        d.dialog('destroy');
                                        $('#datagrid-categories').datagrid('reload');
                                    } else {
                                    	$.messager.alert('Sorry', r.message, 'error');
                                    }
                                }
                            });
                        }
                    },{
                        text : 'chanel',
                        iconCls : 'icon-cancel',
                        handler : function() {
                            dialogCon.dialog('close');
                        }
                    }],
                    onClose : function() {
                        $(this).dialog('destroy');
                    }
                });
            }
        },'-',{
            id:'btnedit',
            text:'Edit Category',
            iconCls:'icon-edit',
            handler:function(){
                var rows = $('#datagrid-categories').datagrid('getSelections');
                if (rows.length == 0) {
                	$.messager.alert('Please', 'please select one category', 'info');
                    return;
                }
                var dialogCon = $('<div></div>');
                dialogCon.dialog({
                    href: 'editcategory?id=' + rows[0].id,
                    width: 600,
                    //height: 400,
                    modal: true,
                    title: 'Edit',
                    buttons: [{
                        text: 'submit',
                        iconCls: 'icon-ok',
                        handler: function() {
                            var d = $(this).closest('.window-body');
                            $('#edit-form-category').form('submit', {
                                url: 'editcategory',
                                success: function(result) {
                                    var r = $.parseJSON(result);
                                    if (r.success) {
                                        d.dialog('destroy');
                                        $('#datagrid-categories').datagrid('reload');
                                        $('#datagrid-categories').datagrid('unselectAll');
                                    } else {
                                        $.messager.alert('Sorry', r.message, 'error');
                                    }
                                }
                            });
                        }
                    },{
                        text : 'chanel',
                        iconCls : 'icon-cancel',
                        handler : function() {
                            dialogCon.dialog('close');
                        }
                    }],
                    onClose: function() {
                        $(this).dialog('destroy');
                    }
                });
            }
        },'-',{
            id:'btnremove',
            text:'Remove Category',
            iconCls:'icon-remove',
            handler:function(){
                var grid = $('#datagrid-categories');
                var rows = grid.datagrid('getSelections');
                var selUids = [];
                for (var i = 0; i < rows.length; i++) {
                   selUids.push(rows[i].id);
                }
                if (selUids.length > 0) {
                    $.messager.confirm('Question', 'Are you sure to remove category: ' + selUids.join(',') + '? all posts on it will be deleted!',
                    function(b) {
                        if (b) {
                            $.ajax({
     	                        url: 'removecategory',
     	                        type:'POST',
     	                        data: {
     	                            id: selUids
     	                        },
     	                        cache: false,
     	                        dataType: 'JSON',
     	                        success: function(r) {
     	                            if (r.success) {
     	                                grid.datagrid('unselectAll');
     	                                grid.datagrid('reload');
     	                            } else {
      	                                $.messager.alert('Sorry', r.message, 'error');
     	                            }
     	                        }
     	                    });
     	                }
                    });
                 } else {
                     $.messager.alert('Notice', 'pls select a category', 'info');
                 };
              }
        }
        ],
        columns:[[
        {
            field:'id',
            align:'center',
            title:'',
            width:20,
        },
        {
            field:'category_name',
            align:'center',
            title:'name',
            width:80
        }
        ]],
        frozenColumns:[[
                {
                    field:'ck',
                    checkbox:true
                }
            ]]
    });
    var p = $('#datagrid-categories').datagrid('getPager');
    $(p).pagination({
        pageSize: 10,
        pageList: [10,20,30,40,50,100],
        beforePageText: '',
        afterPageText: '',
        displayMsg: '{from} - {to} of {total}'
    })
})
</script>
