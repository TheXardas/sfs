<?
namespace DateHelper;

/**
 * Функция возвращает красивый формат даты типа "вчера в 12:30".
 *
 * @param $DateTime
 * @param bool $ShortFormat
 * @param bool $WhenModifier
 * @param bool $weekDaysInsteadOfDates
 *
 * @return string
 */
function formatBeautyTime( $DateTime, $ShortFormat = false, $WhenModifier = false, $weekDaysInsteadOfDates = false )
{
	$time = strtotime( $DateTime );
	$timeStr = date( 'H:i', $time );
	if ( $timeStr === '00:00' && ! strpos( $DateTime, ':' ) ) {
		return formatBeauty( $DateTime, $WhenModifier, $weekDaysInsteadOfDates );
	}
	$dateStr = formatBeauty( $DateTime, $WhenModifier, $weekDaysInsteadOfDates );
	if ( $ShortFormat )
	{
		if ( date( 'Y-m-d', $time ) == date( 'Y-m-d' ) ) {
			return $timeStr;
		}
		else {
			return $dateStr;
		}
	}
	else {
		return $dateStr.' в '.$timeStr;
	}
}

/**
 * Отдаёт красивую дату
 *
 * @param string|\DateTime $DateTime дата в стандартном строковом формате или объект времени
 *
 * @return string
 */
function formatBeauty( $DateTime = '' )
{
	static $cachedDays = NULL;
	if ( ! $DateTime ) {
		return '';
	}
	$date = $DateTime instanceof \DateTime ? $DateTime : new \DateTime( $DateTime );
	$dateStr = $date->format( 'Y-m-d' );

	if ($cachedDays === NULL) {
		$cachedDays = array();
		$now = new \DateTime();
		// today
		$cachedDays[$now->format( 'Y-m-d' )] = 'сегодня';
		// yesterday
		$now->modify( '-1 day' );
		$cachedDays[$now->format( 'Y-m-d' )] = 'вчера';
		// tomorrow
		$now->modify( '+2 days' );
		$cachedDays[$now->format( 'Y-m-d' )] = 'завтра';
	}
	return isset($cachedDays[$dateStr]) ? $cachedDays[$dateStr] :
		dateFormatBeauty($dateStr);
}

/**
 * Отдаёт красивое представление даты
 *
 * @param string $DateTime дата в стандартном строковом формате
 * @return string
 */
function dateFormatBeauty( $DateTime )
{
	if ( NULL === $DateTime ) {
		return '';
	}
	$info = date_parse($DateTime);
	$year = $info['year'] == date('Y') ? '' : $info['year'];
	$month = getMonthR($info['month']);

	return $info['day'] . ' ' . $month . (empty($year) ? '' : ' ' . $year);
}

/**
 * Возвращает месяц в родительском падеже по его (месяца) порядковому номеру
 *
 * @param $monthNumber
 *
 * @return mixed
 */
function getMonthR($monthNumber) {
	$months = [
		'1' => 'января',
		'2' => 'февраля',
		'3' => 'марта',
		'4' => 'апреля',
		'5' => 'мая',
		'6' => 'июня',
		'7' => 'июля',
		'8' => 'августа',
		'9' => 'сентября',
		'10' => 'октября',
		'11' => 'ноября',
		'12' => 'декабря',
	];
	return $months[$monthNumber];
}