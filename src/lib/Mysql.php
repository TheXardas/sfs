<?
namespace Mysql;

// TODO энкапсулировать в отдельный драйвер mysql, чтобы по всему проекту был абстрактный DB.
/**
 * Возвращает первую попавшую строку из select'а.
 *
 * @param $connect
 * @param $table
 * @param array $columns
 * @param array $where
 * @param array $orderBy
 * @param bool $forUpdate
 *
 * @return mixed
 * @throws \Exception
 */
function selectOne($connect, $table, array $columns, array $where, array $orderBy = [], $forUpdate = false) {
	$result = select($connect, $table, $columns, $where, $orderBy, 1, null, null, $forUpdate);
	return reset($result);
}

/**
 * Считает количество строк в таблице, удовлетворяющих условиям
 *
 * @param $connect
 * @param $table
 * @param array $where
 *
 * @return mixed
 */
function count($connect, $table, array $where) {
	return (int) selectOne($connect, $table, ['cnt' => 'COUNT(*)'], $where)['cnt'];
}

/**
 * Выполнить селект и вернуть в виде массива ассоциативных массивов.
 *
 * @param resource $connect
 * @param string $table
 * @param array $columns Массив колонок вида алиас => колонка
 * @param array $where Массив точных условий вида колонка => значение
 * @param array $orderBy Массив параметров для сортировки вида колонка => направление сортировки
 * @param int $limit Сколько записей нужно выбрать
 * @param int $offset Начиная с какой записи будем выбирать
 * @param bool $assocById Вернуть массив, в котором ключи будут являться идентификаторами
 * @param bool $forUpdate Использовать ли залочку FOR UPDATE
 * @return array
 * @throws \Exception
 */
function select($connect, $table, array $columns, array $where, array $orderBy = [], $limit = NULL, $offset = NULL, $assocById = false, $forUpdate = false)
{
	$columnsStr = _getColumnsStringFromArray($columns);
	if (!$columnsStr) {
		throw new \Exception('Empty column list for query.');
	}

	$whereStr = _getConditionStringFromArray($connect, $where);
	if (!$whereStr) {
		throw new \Exception('Empty condition for query.');
	}

	$orderByStr = _getOrderByStringFromArray($orderBy);

	$sql = "SELECT $columnsStr FROM $table WHERE $whereStr";
	if ($orderByStr) {
		$sql .= " ORDER BY $orderByStr";
	}

	$limit = (int) $limit;
	if ($limit > 0 && is_numeric($limit)) {
		$sql .= " LIMIT ";
		$offset = (int) $offset;
		if ($offset > 0 && is_numeric($offset)) {
			$sql .= "$offset, ";
		}
		$sql .= $limit;
	}

	if ($forUpdate) {
		$sql .= ' FOR UPDATE';
	}

	$res = _query($connect, $sql);

	$result = [];
	while ($row = mysqli_fetch_assoc($res)) {
		if ($assocById) {
			$result[$row['id']] = $row;
		}
		else {
			$result[] = $row;
		}
	}
	return $result;
}

/**
 * Выполняет вставку новой строки в таблицу
 *
 * @param $connect
 * @param $table
 * @param array $values Значения новой строки вида колонка => значение
 *
 * @return int|string Идентификатор новой строчки
 * @throws \Exception
 */
function insert($connect, $table, array $values) {
	$valuesStr = _getConditionStringFromArray($connect, $values, true);
	if (!$valuesStr) {
		throw new \Exception('No values for insert query');
	}

	$sql = "INSERT INTO $table SET $valuesStr";

	_query($connect, $sql);

	return mysqli_insert_id($connect);
}

/**
 * Обновляет набор строк в БД
 *
 * @param $connect
 * @param $table
 * @param array $values Новые значения в формате колонка => значение
 * @param array $where Условие, какие строчки заменять вида колонка => значение
 *
 * @return int Количество измененных строк
 * @throws \Exception
 */
function update($connect, $table, array $values, array $where) {
	$valuesStr = _getConditionStringFromArray($connect, $values, true);
	if (!$valuesStr) {
		throw new \Exception('No values for insert query');
	}

	$whereStr = _getConditionStringFromArray($connect, $where);

	$sql = "UPDATE $table SET $valuesStr WHERE $whereStr";

	_query($connect, $sql);

	return mysqli_affected_rows($connect);
}


/**
 * Подготавливает значение колонки к подстановке в sql-запрос
 *
 * @todo хорошенько перепроверить тут всё.
 * @param $connect
 * @param $value
 * @return string
 */
function _escape($connect, $value) {
	if (is_numeric($value)) {
		return $value;
	}

	if (is_string($value)) {
		$value = mysqli_real_escape_string($connect, $value);
	}

	return "'$value'";
}

/**
 *
 * @param array $columns
 * @return string
 */
function _getColumnsStringFromArray(array $columns) {
	$sql = '';
	foreach ($columns as $alias => $column) {
		if ($sql) {
			$sql .= ', ';
		}
		$sql .= "$column $alias";
	}
	return $sql;
}

/**
 * Обрабатывает массив условий колонка => значение, возвращает sql-подстроку для подстановки в where
 *
 * @todo дописать возможность использовать null.
 *
 * @param resource $connect
 * @param array $conditions
 * @param bool $forUpdate
 *
 * @return string
 */
function _getConditionStringFromArray($connect, array $conditions, $forUpdate = false) {
	$sql = '';
	$separator = $forUpdate ? ', ' : ' AND ';
	foreach ($conditions as $column => $value)
	{
		if ($sql) {
			$sql .= $separator;
		}
		if (is_array($value)) {
			$valuesStr = '';
			foreach ($value as $subValue) {
				if ($valuesStr) {
					$valuesStr .= ', ';
				}
				$valuesStr .= _escape($connect, $subValue);
			}
			if ($valuesStr) {
				$sql .= "$column IN ( $valuesStr )";
			}
		} else {
			$value = _escape($connect, $value);
			$sql .= "$column = $value";
		}
	}
	return $sql;
}

/**
 * Обрабатывает массив колонок для сортировки колонка => направление, возвращая sql-подстроку для подставки в order by
 * @param array $orderBy
 * @return string
 */
function _getOrderByStringFromArray(array $orderBy) {
	$sql = '';
	foreach ($orderBy as $column => $direction) {
		if ($sql) {
			$sql .= ', ';
		}
		if ($direction !== 'DESC') {
			$direction = 'ASC';
		}

		$sql .= "$column $direction";
	}
	return $sql;
}

function _query($connect, $sql) {
	$result = mysqli_query($connect, $sql);
	if ($result === false) {
		$error = mysqli_error($connect);
		throw new \Exception(sprintf('Failed quering DB: %s', $error));
	}
	return $result;
}