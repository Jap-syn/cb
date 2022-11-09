<h3>array</h3>
<table border="1" cellpadding="2" cellspacing="0">
	<tbody>
<?php
foreach( $this->tableArrayData as $table_row ) {
	echo "<tr>";
	
	foreach( $table_row as $column_key => $column_value ) {
		echo "<td> $column_key : $column_value </td>";
	}
	
	echo "</tr>";
}
?>
	</tbody>
</table>
