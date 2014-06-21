<form class="tableform" id="edit-form-category" method="post">
    <input type="hidden" name="id" value="<?php echo $category['id'];?>" />
    <div class="division">
        <table width="100%" cellspacing="0" cellpadding="0" border="0">
            <tbody>
                <tr>
                    <th>name</th>
                    <td>
                        <input type="text" class="easyui-validatebox" data-options="required:true" name="category[category_name]" value="<?php echo $category['category_name'];?>" style="width:240px;">
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</form>
