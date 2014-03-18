<table id="datagrid-blogs">
</table>
<script type="text/javascript">
$(function(){
    var height = $('.indexcenter').height();
    $('#datagrid-blogs').datagrid({
        url:'blogs',
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
            text:'New Post',
            iconCls:'icon-add',
            handler:function(){
            	var dialogCon = $('<div></div>');
                dialogCon.dialog({
                    href: 'newblog',
                    width:600,
                    //height:400,
                    modal : true,
                    resizable:true,
                    collapsible:true,
                    maximizable:true,
                    title:'New post',
                    buttons : [{
                        text : 'submit',
                        iconCls : 'icon-ok',
                        handler : function() {
                            var d = $(this).closest('.window-body');
                            $('#add-form-blog').form('submit', {
                                url : 'newblog',
                                success : function(result) {
                                    var r = $.parseJSON(result);
                                    if (r.success) {
                                        d.dialog('destroy');
                                        $('#datagrid-blogs').datagrid('reload');
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
            text:'Edit post',
            iconCls:'icon-edit',
            handler:function(){
                var rows = $('#datagrid-blogs').datagrid('getSelections');
                if (rows.length == 0) {
                	$.messager.alert('Please', 'please select one blog', 'info');
                    return;
                }
                var dialogCon = $('<div></div>');
                dialogCon.dialog({
                    href: 'editBlog?id=' + rows[0].id,
                    width: 600,
                    //height: 400,
                    modal: true,
                    title: 'Edit',
                    buttons: [{
                        text: 'submit',
                        iconCls: 'icon-ok',
                        handler: function() {
                            var d = $(this).closest('.window-body');
                            $('#edit-form-blog').form('submit', {
                                url: 'editBlog',
                                success: function(result) {
                                    var r = $.parseJSON(result);
                                    if (r.success) {
                                        d.dialog('destroy');
                                        $('#datagrid-blogs').datagrid('reload');
                                        $('#datagrid-blogs').datagrid('unselectAll');
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
            text:'Remove Post',
            iconCls:'icon-remove',
            handler:function(){
                var grid = $('#datagrid-blogs');
                var rows = grid.datagrid('getSelections');
                var selUids = [];
                for (var i = 0; i < rows.length; i++) {
                   selUids.push(rows[i].id);
                }
                if (selUids.length > 0) {
                    $.messager.confirm('Question', 'Are you sure to remove blog: ' + selUids.join(',') + '?',
                    function(b) {
                        if (b) {
                            $.ajax({
     	                        url: 'removeBlog',
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
                     $.messager.alert('Notice', 'pls select a blog', 'info');
                 };
              }
        }
        ],
        columns:[[
        {
            field:'id',
            align:'center',
            title:'',
            width:10,
        },
        {
            field:'title',
            align:'center',
            title:'title',
            width:80
        },
        {
            field:'addtime',
            align:'center',
            title:'create time',
            width:10,
        },
        ]],
        frozenColumns:[[
                {
                    field:'ck',
                    checkbox:true
                }
            ]]
    });
    var p = $('#datagrid-blogs').datagrid('getPager');
    $(p).pagination({
        pageSize: 10,
        pageList: [10,20,30,40,50,100],
        beforePageText: '',
        afterPageText: '',
        displayMsg: '{from} - {to} of {total}'
    })
})
</script>
