<form method="post" action="">
    <input type="hidden" name="action" value="saveevents">
    <table class="calendar_input <?=$this->tableclass()?>">
        <tr>
            <td colspan="<?=$this->columns()?>">
                <input class="submit" type="submit" name="send" value="<?=$this->saveLabel()?>">
            </td>
            <td style="text-align: right; width: 16px;">
                <button name="add[0]" value="add" title="<?=$this->text('label_add_event')?>"><span class="fa fa-plus"></span></button>
            </td>
        </tr>
        <?=$this->table()?>
        <tr>
            <td colspan="<?=$this->columns()?>">
                <input class="submit" type="submit" name="send" value="<?=$this->saveLabel()?>">
            </td>
            <td>
            <button name="add[0]" value="add" title="<?=$this->text('label_add_event')?>"><span class="fa fa-plus"></span></button>
            </td>
        </tr>
    </table>
</form>
