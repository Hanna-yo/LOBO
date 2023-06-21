<?php
function sql_build_array($query, $assoc_ary = false)
{
	if ( !is_array($assoc_ary) )
	{
		return false;
	}

	$query = strtoupper(trim($query));

	$fields = $values = array();

	if ( $query == 'INSERT' )
	{
		foreach ( $assoc_ary as $key => $var )
		{
			$fields[] = $key;
			$values[] = ':'.$key;
		}

		$query = ' (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')';
	}
	else if ($query == 'UPDATE' || $query == 'SELECT')
	{
		$values = array();
		foreach ($assoc_ary as $key => $var)
		{
			$values[] = $key.' = :'.$key ;
		}
		$query = implode(($query == 'UPDATE') ? ', ' : ' AND ', $values);
	}

	return $query;
}
function sql_query($rs_name)
{
	global $pdo;

	$rs_name->execute();

	$aTemp_Info = $rs_name->errorInfo();

	if ( !( isset($aTemp_Info[0] ) && ( $aTemp_Info[0] == '00000' ) ) )
	{
		$sSQL_Code = isset($aTemp_Info[1]) ? $aTemp_Info[1] : '';
		$sSQL_Message = isset($aTemp_Info[2]) ? $aTemp_Info[2] : '';

		$sError_Msg = 'SQL Error : ' . '['. $sSQL_Code  .']' . $sSQL_Message;

		$pdo->rollBack();
		echo $sError_Msg;
		exit;
	}
}

function sql_build_value($rs_name, $assoc_ary = false)
{
	if ( !is_array($assoc_ary) )
	{
		return false;
	}
	foreach ($assoc_ary as $k => $v)
	{
		if (is_numeric($v)){
			$rs_name->bindValue(':'.$k, $v,	PDO::PARAM_INT);
			#echo 'int';
		}else{
			$rs_name->bindValue(':'.$k, $v,	PDO::PARAM_STR);
			#echo 'str';
		}
	}
}

function sql_limit($nStart = 0, $nCount = 1)
{
	$sLimit = '';
	if ($nCount == 1)
	{
		$sLimit = 'LIMIT ' . $nCount;
	}
	else
	{
		$sLimit = 'LIMIT ' . $nStart .','. $nCount;
	}
	$sLimit = $sLimit . ' ';
	return $sLimit;
}
?>