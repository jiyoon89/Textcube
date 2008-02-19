<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
class Statistics {
	function getStatistics($blogid) {
		global $database;
		$stats = array('total' => 0, 'today' => 0, 'yesterday' => 0);
		$result = mysql_query("select visits from {$database['prefix']}BlogStatistics where blogid = $blogid");
		if (mysql_num_rows($result) == 1)
			list($stats['total']) = mysql_fetch_array($result);
		$result = mysql_query("select visits from {$database['prefix']}DailyStatistics where blogid = $blogid and `date` = " . Timestamp::getDate());
		if (mysql_num_rows($result) == 1)
			list($stats['today']) = mysql_fetch_array($result);
		$result = mysql_query("select visits from {$database['prefix']}DailyStatistics where blogid = $blogid and `date` = " . Timestamp::getDate(time() - 86400));
		if (mysql_num_rows($result) == 1)
			list($stats['yesterday']) = mysql_fetch_array($result);
		return $stats;
	}

	function getDailyStatistics($period) {
		global $database, $blogid;
		return POD::queryAll("SELECT date, visits FROM {$database['prefix']}DailyStatistics WHERE blogid = $blogid AND LEFT(date, 6) = $period ORDER BY date DESC");
	}
	
	function getWeeklyStatistics() {
		global $database, $blogid;
		$now_day = date('Ymd', strtotime("now"));
		$old_day = date('Ymd', strtotime("-1 week"));
		return POD::queryAll("SELECT date, visits FROM {$database['prefix']}DailyStatistics WHERE blogid = $blogid AND  date BETWEEN $old_day AND $now_day ORDER BY date DESC");
	}

	function getMonthlyStatistics($blogid) {
		global $database;
		$statistics = array();
		if ($result = mysql_query("select left(date, 6) date, sum(visits) visits from {$database['prefix']}DailyStatistics where blogid = $blogid group by left(date, 6) order by date desc")) {
			while ($record = mysql_fetch_array($result))
				array_push($statistics, $record);
		}
		return $statistics;
	}

	function getRefererStatistics($blogid) {
		global $database;
		$statistics = array();
		if ($result = mysql_query("select host, count from {$database['prefix']}RefererStatistics where blogid = $blogid order by count desc limit 20")) {
			while ($record = mysql_fetch_array($result))
				array_push($statistics, $record);
		}
		return $statistics;
	}

	function getRefererLogsWithPage($page, $count) {  
		global $database, $blogid;
		return Statistics::fetchWithPaging("SELECT host, url, referred FROM {$database['prefix']}RefererLogs WHERE blogid = $blogid ORDER BY referred DESC", $page, $count);  
	}  

	function getRefererLogs() {
		global $database, $blogid;
		return POD::queryAll("SELECT host, url, referred FROM {$database['prefix']}RefererLogs WHERE blogid = $blogid ORDER BY referred DESC LIMIT 1500");
	}

	function updateVisitorStatistics($blogid) {
		global $database, $blogURL;
		if (!fireEvent('UpdatingVisitorStatistics', true))
			return;
		if (doesHaveOwnership())
			return;
		$id = session_id();
		$result = mysql_query("select blog from {$database['prefix']}SessionVisits where id = '$id' and address = '{$_SERVER['REMOTE_ADDR']}' and blog = $blogid");
		if ($result && (mysql_num_rows($result) > 0))
			return;
		if (mysql_query("insert into {$database['prefix']}SessionVisits values('$id', '{$_SERVER['REMOTE_ADDR']}', $blogid)") && (mysql_affected_rows() > 0)) {
			mysql_query("update {$database['prefix']}BlogStatistics set visits = visits + 1 where blogid = $blogid");
			if (mysql_affected_rows() == 0) {
				if (mysql_query("update {$database['prefix']}BlogStatistics set visits = visits + 1 where blogid = $blogid") || (mysql_affected_rows() == 0))
					mysql_query("insert into {$database['prefix']}BlogStatistics values($blogid, 1)");
			}
			$period = Timestamp::getDate();
			mysql_query("update {$database['prefix']}DailyStatistics set visits = visits + 1 where blogid = $blogid and `date` = $period");
			if (mysql_affected_rows() == 0) {
				if (!mysql_query("insert into {$database['prefix']}DailyStatistics values($blogid, $period, 1)") || (mysql_affected_rows() == 0))
					mysql_query("update {$database['prefix']}DailyStatistics set visits = visits + 1 where blogid = $blogid and `date` = $period");
			}
			if (!empty($_SERVER['HTTP_REFERER'])) {
				$referer = parse_url($_SERVER['HTTP_REFERER']);
				if (!empty($referer['host']) && (($referer['host'] != $_SERVER['HTTP_HOST']) || (strncmp($referer['path'], $blogURL, strlen($blogURL)) != 0))) {
					requireComponent('Textcube.Data.Filter');
					if (Filter::isFiltered('ip', $_SERVER['REMOTE_ADDR']) || Filter::isFiltered('url', $_SERVER['HTTP_REFERER']))
						return;
					if (!fireEvent('AddingRefererLog', true, array('host' => $referer['host'], 'url' => $_SERVER['HTTP_REFERER'])))
						return;
					$host = POD::escapeString(UTF8::lessenAsEncoding($referer['host'], 64));
					$url = POD::escapeString(UTF8::lessenAsEncoding($_SERVER['HTTP_REFERER'], 255));
					mysql_query("insert into {$database['prefix']}RefererLogs values($blogid, '$host', '$url', UNIX_TIMESTAMP())");
					mysql_query("delete from {$database['prefix']}RefererLogs where referred < UNIX_TIMESTAMP() - 604800");
					if (!mysql_query("update {$database['prefix']}RefererStatistics set count = count + 1 where blogid = $blogid and host = '$host'") || (mysql_affected_rows() == 0))
						mysql_query("insert into {$database['prefix']}RefererStatistics values($blogid, '$host', 1)");
				}
			}
		}
	}

	function setTotalStatistics($blogid) {
		global $database;
		POD::execute("DELETE FROM {$database['prefix']}DailyStatistics WHERE blogid = $blogid");
		$prevCount = POD::queryCell("SELECT visits FROM {$database['prefix']}BlogStatistics WHERE blogid = $blogid");
		if ((!is_null($prevCount)) && ($prevCount == 0))
			return true;
		mysql_query("update {$database['prefix']}BlogStatistics set visits = 0 where blogid = $blogid");
		if (mysql_affected_rows() == 0)
			mysql_query("insert into {$database['prefix']}BlogStatistics values($blogid, 0)");
		return mysql_affected_rows() ? true : false;
	}
	
	function fetchWithPaging($sql, $page, $count, $url = null, $prefix = '?page=') {
		global $folderURL;
		requireComponent('Eolin.PHP.Core');
		if (is_null($url))
			$url = $folderURL;
		$paging = array('url' => $url, 'prefix' => $prefix, 'postfix' => '');
		if (empty($sql))
			return array(array(), $paging);
		if (preg_match('/\s(FROM.*)$/si', $sql, $matches))
			$from = $matches[1];
		else
			return array(array(), $paging);
		$paging['total'] = POD::queryCell("SELECT COUNT(*) $from");
		if (is_null($paging['total']))
			return array(array(), $paging);
		$paging['pages'] = intval(ceil($paging['total'] / $count));
		$paging['page'] = is_numeric($page) ? $page : 1;
		if ($paging['page'] > $paging['pages']) {
			$paging['page'] = $paging['pages'];
			if ($paging['pages'] > 0)
				$paging['prev'] = $paging['pages'] - 1;
			//return array(array(), $paging);
		}
		if ($paging['page'] > 1)
			$paging['prev'] = $paging['page'] - 1;
		if ($paging['page'] < $paging['pages'])
			$paging['next'] = $paging['page'] + 1;
		$offset = ($paging['page'] - 1) * $count;
		return array(POD::queryAll("$sql LIMIT $offset, $count"), $paging);
	}
}
?>
