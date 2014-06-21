<form class="tableform" id="add-form-blog" method="post">
    <div class="division">
        <table width="100%" cellspacing="0" cellpadding="0" border="0">
            <tbody>
                <tr>
                    <th>title</th>
                    <td>
                        <input class="easyui-validatebox" data-options="required:true" type="text" name="blog[title]" style="width:240px;">
                    </td>
                </tr>
                <tr>
                    <th>category</th>
                    <td>
                        <select class="easyui-combobox" data-options="required:true" name="blog[category_id]" style="width:200px;">
                            <?php foreach ($catetories as $category):?>
                            <option value="<?php echo $category['id']?>"><?php echo $category['category_name']?></option>
                            <?php endforeach;?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>content</th>
                    <td>
                        <textarea class="easyui-validatebox" data-options="required:true" name="blog[content]" rows="12" cols="80" style="width: 100%;height: 70px"></textarea>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</form>