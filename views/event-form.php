<form method="post" action="">
    <input type="hidden" name="action" value="saveevents">
    <table class="calendar_input <?=$this->tableclass()?>">
        <tr>
            <td colspan="<?=$this->columns()?>">
                <input class="submit" type="submit" name="send" value="<?=$this->saveLabel()?>">
            </td>
            <td style="text-align: right; width: 16px;">
                <input type="image" src="<?=$this->addIcon()?>" style="width: 16px; height: 16px;" name="add[0]" value="add" alt="Add entry">
            </td>
        </tr>
        <?=$this->table()?>
        <tr>
            <td colspan="<?=$this->columns()?>">
                <input class="submit" type="submit" name="send" value="<?=$this->saveLabel()?>">
            </td>
            <td>
                <input type="image" src="<?=$this->addIcon()?>" style="width: 16px; height: 16px;" name="add[0]" value="add" alt="Add entry">
            </td>
        </tr>
    </table>
</form>
