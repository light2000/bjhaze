<table id="datagrid-comments">
</table>
<script type="text/javascript">
$(function(){
    var height = $('.indexcenter').height();
    $('#datagrid-comments').datagrid({
        url:'comments',
        idField:'id',
        pagination:true,
        rownumbers:false,
        fitColumns:true,
        checkbox:true,
        height:height-60,
        selectOnCheck:true,
        //singleSelect:true,
        toolbar:[
        {
            id:'btnremove',
            text:'Remove Comment',
            iconCls:'icon-remove',
            handler:function(){
                var grid = $('#datagrid-comments');
                var rows = grid.datagrid('getSelections');
                var selUids = [];
                for (var i = 0; i < rows.length; i++) {
                   selUids.push(rows[i].id);
                }
                if (selUids.length > 0) {
                    $.messager.confirm('Question', 'Are you sure to remove comments: ' + selUids.join(',') ,
                    function(b) {
                        if (b) {
                            $.ajax({
     	                        url: 'removecomment',
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
                     $.messager.alert('Notice', 'pls select a comment', 'info');
                 }
            }
        },'-'],
        columns:[[
        {
            field:'id',
            align:'center',
            title:'',
            width:20,
        },
        {
            field:'title',
            align:'center',
            title:'Blog Title',
            width:20,
        },
        {
            field:'content',
            align:'center',
            title:'Comment',
            width:80
        },
        {
            field:'addtime',
            align:'center',
            title:'Create Time',
            width:80
        }
        ]],
    });
    var p = $('#datagrid-comments').datagrid('getPager');
    $(p).pagination({
        pageSize: 10,
        pageList: [10,20,30,40,50,100],
        beforePageText: '',
        afterPageText: '',
        displayMsg: '{from} - {to} of {total}'
    })
})
</script>
