<?php
require_once('./support/init.php');

class User extends fActiveRecord { }
class Group extends fActiveRecord { }
class Artist extends fActiveRecord { }
class Album extends fActiveRecord { }
class Song extends fActiveRecord { }
class UserDetail extends fActiveRecord { }
class RecordLabel extends fActiveRecord { } 
class FavoriteAlbum extends fActiveRecord { }
class InvalidTable extends fActiveRecord { }
class Event extends fActiveRecord { }

function Album($album_id)
{
	return new Album($album_id);	
}

function _tally($value, $record)
{
	$value += $record->getTimesLoggedIn();
	return $value;	
}

class fRecordSetTest extends PHPUnit_Framework_TestSuite
{
	public static function suite()
	{
		return new fRecordSetTest('fRecordSetTestChild');
	}
	
	protected function setUp()
	{
		if (defined('SKIPPING')) {
			return;
		}
		$db = new fDatabase(DB_TYPE, DB, DB_USERNAME, DB_PASSWORD, DB_HOST, DB_PORT); 
		$db->execute(file_get_contents(DB_SETUP_FILE));
		$db->execute(file_get_contents(DB_EXTENDED_SETUP_FILE));
		fORMDatabase::attach($db);
		$this->sharedFixture = $db;
	}
 
	protected function tearDown()
	{
		if (defined('SKIPPING')) {
			return;
		}
		$db = $this->sharedFixture;
		$db->execute(file_get_contents(DB_EXTENDED_TEARDOWN_FILE));		
		$db->execute(file_get_contents(DB_TEARDOWN_FILE));
	}
}

class fRecordSetTestChild extends PHPUnit_Framework_TestCase
{
	public $db;
	
	public function setUp()
	{
		if (defined('SKIPPING')) {
			$this->markTestSkipped();
		}
		$this->db = $this->sharedFixture;
		if (defined('MAP_TABLES')) {
			fORM::mapClassToTable('User', 'user');
			fORM::mapClassToTable('Group', 'group');
			fORM::mapClassToTable('Artist', 'popular_artists');
			fORM::mapClassToTable('Album', 'records');
		}
	}
	
	public function tearDown()
	{
		if (defined('SKIPPING')) {
			return;
		}
		fORMDatabase::retrieve()->enableDebugging(FALSE);
		fORMRelated::reset();
	}
	
	public function testCount()
	{
		$set = fRecordSet::build('User');
		$this->assertEquals(4, $set->count());
	}
	
	public function testCountNonLimited()
	{
		$set = fRecordSet::build('User', NULL, NULL, 2);
		$this->assertEquals(2, $set->count());
		$this->assertEquals(4, $set->count(TRUE));
	}
	
	public function testCountSlice()
	{
		$set = fRecordSet::build('User');
		$set = $set->slice(0, 2);
		$this->assertEquals(2, $set->count());
		$this->assertEquals(2, $set->count(TRUE));
	}
	
	public function testCountFilter()
	{
		$set = fRecordSet::build('User');
		$set = $set->filter(array('getStatus=' => 'Active'));
		$this->assertEquals(3, $set->count());
		$this->assertEquals(3, $set->count(TRUE));
	}
	
	public function testCountDiff()
	{
		$set = fRecordSet::build('User');
		$set = $set->diff(fRecordSet::build('User', array('email_address=' => 'will@flourishlib.com')));
		$this->assertEquals(3, $set->count());
		$this->assertEquals(3, $set->count(TRUE));
	}
	
	public function testCountIntersect()
	{
		$set = fRecordSet::build('User');
		$set = $set->intersect(fRecordSet::build('User', array('email_address=' => 'will@flourishlib.com')));
		$this->assertEquals(1, $set->count());
		$this->assertEquals(1, $set->count(TRUE));
	}
	
	public function testCountUnique()
	{
		$set = fRecordSet::build('User');
		$set = $set->merge(new User(1));
		$set = $set->unique();
		$this->assertEquals(4, $set->count());
		$this->assertEquals(4, $set->count(TRUE));
	}
	
	public function testRememberCountSlice()
	{
		$set = fRecordSet::build('User');
		$set = $set->slice(0, 2, TRUE);
		$this->assertEquals(2, $set->count());
		$this->assertEquals(4, $set->count(TRUE));
	}
	
	public function testRememberCountFilter()
	{
		$set = fRecordSet::build('User');
		$set = $set->filter(array('getStatus=' => 'Active'), TRUE);
		$this->assertEquals(3, $set->count());
		$this->assertEquals(4, $set->count(TRUE));
	}
	
	public function testRememberCountDiff()
	{
		$set = fRecordSet::build('User');
		$set = $set->diff(fRecordSet::build('User', array('email_address=' => 'will@flourishlib.com')), TRUE);
		$this->assertEquals(3, $set->count());
		$this->assertEquals(4, $set->count(TRUE));
	}
	
	public function testRememberCountIntersect()
	{
		$set = fRecordSet::build('User');
		$set = $set->intersect(fRecordSet::build('User', array('email_address=' => 'will@flourishlib.com')), TRUE);
		$this->assertEquals(1, $set->count());
		$this->assertEquals(4, $set->count(TRUE));
	}
	
	public function testRememberCountUnique()
	{
		$set = fRecordSet::build('User');
		$set = $set->merge(new User(1));
		$set = $set->unique(TRUE);
		$this->assertEquals(4, $set->count());
		$this->assertEquals(5, $set->count(TRUE));
	}
	
	public function testCall()
	{
		$set = fRecordSet::build('User');
		$this->assertEquals(
			array(
				'will@flourishlib.com',
				'john@smith.com',
				'bar@example.com',
				'foo@example.com'
			),
			$set->call('getEmailAddress')
		);
	}
	
	public function testCallWithParameter()
	{
		$set = fRecordSet::build('User');
		$this->assertEquals(
			array(
				'5/1/08',
				'2/12/08',
				'1/1/08',
				'3/2/08'
			),
			$set->call('prepareDateCreated', 'n/j/y')
		);
	}
	
	public function testGetPrimaryKeys()
	{
		$set = fRecordSet::build('User');
		$this->assertEquals(
			array(1, 2, 3, 4),
			$set->getPrimaryKeys()
		);
	}
	
	public function testGetRecords()
	{
		$set = fRecordSet::build('User');
		$records = $set->getRecords();
		$this->assertEquals(TRUE, $records[0] instanceof User);
		$this->assertEquals(TRUE, $records[1] instanceof User);
		$this->assertEquals(TRUE, $records[2] instanceof User);
		$this->assertEquals(TRUE, $records[3] instanceof User);
		$this->assertEquals(4, count($records));
	}
	
	public function testPrebuildManyToMany()
	{
		fORMRelated::setOrderBys('User', 'Group', array('group_id' => 'desc'), 'users_groups');
		
		$set = fRecordSet::build('User');
		$set->prebuildGroups('users_groups');
		
		ob_start();
		
		fORMDatabase::retrieve()->enableDebugging(TRUE);
		foreach ($set as $user) {
			$group_ids = $user->listGroups('users_groups');
			switch ($user->getUserId()) {
				case 1:
					$expected_group_ids = array(2, 1);
					break;
				case 2:
					$expected_group_ids = array(2, 1);
					break;
				case 3:
					$expected_group_ids = array(1);
					break;
				case 4:
					$expected_group_ids = array(1);
					break;				
			}
			$this->assertEquals($expected_group_ids, $group_ids);
		}
		fORMDatabase::retrieve()->enableDebugging(FALSE);
		
		$output = ob_get_clean();
		$this->assertEquals('', $output);
	}
	
	public function testPrebuildOneToMany()
	{
		fORMRelated::setOrderBys('Artist', 'Album', array('album_id' => 'desc'));
		
		$set = fRecordSet::build('Artist');
		$set->prebuildAlbums();
		
		ob_start();
		
		fORMDatabase::retrieve()->enableDebugging(TRUE);
		foreach ($set as $artist) {
			$album_ids = $artist->listAlbums();
			switch ($artist->getArtistId()) {
				case 1:
					$expected_album_ids = array(1);
					break;
				case 2:
					$expected_album_ids = array(3, 2);
					break;
				case 3:
					$expected_album_ids = array(7, 6, 5, 4);
					break;		
			}
			$this->assertEquals($expected_album_ids, $album_ids);
		}
		fORMDatabase::retrieve()->enableDebugging(FALSE);
		
		$output = ob_get_clean();
		$this->assertEquals('', $output);
	}
	
	public function testPrebuildOneToManyMultiColumn()
	{
		fORMRelated::setOrderBys('User', 'FavoriteAlbum', array('album_id' => 'desc'));
		
		$set = fRecordSet::build('User');
		$set->prebuildFavoriteAlbums();
		
		ob_start();
		
		fORMDatabase::retrieve()->enableDebugging(TRUE);
		foreach ($set as $user) {
			$primary_keys = $user->listFavoriteAlbums();
			switch ($user->getUserId()) {
				case 1:
					$expected_primary_keys = array(
						array('email' => 'will@flourishlib.com', 'album_id' => 7), 
						array('email' => 'will@flourishlib.com', 'album_id' => 4),
						array('email' => 'will@flourishlib.com', 'album_id' => 3),
						array('email' => 'will@flourishlib.com', 'album_id' => 2),
						array('email' => 'will@flourishlib.com', 'album_id' => 1)
					);
					break;
				case 2:
					$expected_primary_keys = array(
						array('email' => 'john@smith.com', 'album_id' => 2)
					);
					break;
				case 3:
					$expected_primary_keys = array();
					break;
				case 4:
					$expected_primary_keys = array();
					break;		
			}
			$this->assertEquals($expected_primary_keys, $primary_keys);
		}
		fORMDatabase::retrieve()->enableDebugging(FALSE);
		
		$output = ob_get_clean();
		$this->assertEquals('', $output);
	}
	
	public function testPrecountManyToMany()
	{
		$set = fRecordSet::build('User');
		$set->precountGroups('users_groups');
		
		ob_start();
		
		fORMDatabase::retrieve()->enableDebugging(TRUE);
		foreach ($set as $user) {
			$count = $user->countGroups('users_groups');
			switch ($user->getUserId()) {
				case 1:
					$expected_count = 2;
					break;
				case 2:
					$expected_count = 2;
					break;
				case 3:
					$expected_count = 1;
					break;
				case 4:
					$expected_count = 1;
					break;		
			}
			$this->assertEquals($expected_count, $count);
		}
		fORMDatabase::retrieve()->enableDebugging(FALSE);
		
		$output = ob_get_clean();
		$this->assertEquals('', $output);
	}
	
	public function testPrecountOneToMany()
	{
		$set = fRecordSet::build('Artist');
		$set->precountAlbums();
		
		ob_start();
		
		fORMDatabase::retrieve()->enableDebugging(TRUE);
		foreach ($set as $artist) {
			$count = $artist->countAlbums();
			switch ($artist->getArtistId()) {
				case 1:
					$expected_count = 1;
					break;
				case 2:
					$expected_count = 2;
					break;
				case 3:
					$expected_count = 4;
					break;	
			}
			$this->assertEquals($expected_count, $count);
		}
		fORMDatabase::retrieve()->enableDebugging(FALSE);
		
		$output = ob_get_clean();
		$this->assertEquals('', $output);
	}
	
	public function testPrecountOneToManyMultiColumn()
	{
		$set = fRecordSet::build('User');
		$set->precountFavoriteAlbums();
		
		ob_start();
		
		fORMDatabase::retrieve()->enableDebugging(TRUE);
		foreach ($set as $user) {
			$count = $user->countFavoriteAlbums();
			switch ($user->getUserId()) {
				case 1:
					$expected_count = 5;
					break;
				case 2:
					$expected_count = 1;
					break;
				case 3:
					$expected_count = 0;
					break;
				case 4:
					$expected_count = 0;
					break;		
			}
			$this->assertEquals($expected_count, $count);
		}
		fORMDatabase::retrieve()->enableDebugging(FALSE);
		
		$output = ob_get_clean();
		$this->assertEquals('', $output);
	}
	
	public function testBuild()
	{
		$set = fRecordSet::build('User');
		$this->assertEquals(
			array(1, 2, 3, 4),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereCondition()
	{
		$set = fRecordSet::build('User', array('email_address=' => 'will@flourishlib.com'));
		$this->assertEquals(
			array(1),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionColumnConcat()
	{
		$set = fRecordSet::build('User', array('first_name||last_name=' => 'WillBond'));
		$this->assertEquals(
			array(1),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionColumnConcatWithString()
	{
		$set = fRecordSet::build('User', array("first_name||' '||last_name=" => 'Will Bond'));
		$this->assertEquals(
			array(1),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionFullyQualified()
	{
		$set = fRecordSet::build('User', array(sprintf('%s.email_address=', fORM::tablize('User')) => 'will@flourishlib.com'));
		$this->assertEquals(
			array(1),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionRelatedTableManyToManyRoute()
	{
		$set = fRecordSet::build('User', array(sprintf('%s{users_groups}.name=', fORM::tablize('Group')) => 'Musicians'));
		$this->assertEquals(
			array(1, 2),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionRelatedTableOneToManyRoute()
	{
		$set = fRecordSet::build('User', array(sprintf('%s{group_leader}.name=', fORM::tablize('Group')) => 'Music Lovers'));
		$this->assertEquals(
			array(1),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionRelatedTableOneToManyRoute2()
	{
		$set = fRecordSet::build('User', array(sprintf('%s{group_founder}.name=', fORM::tablize('Group')) => 'Music Lovers'));
		$this->assertEquals(
			array(2),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionOnceRemovedRelatedTable()
	{
		$set = fRecordSet::build('User', array(sprintf('%s{owns_on_cd}=>songs.track_number>', fORM::tablize('Album')) => 13));
		$this->assertEquals(
			array(1, 4),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionNotEqual()
	{
		$set = fRecordSet::build('User', array('email_address!' => NULL));
		$this->assertEquals(
			array(1, 2, 3, 4),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionNotEqualType2()
	{
		$set = fRecordSet::build('User', array('email_address!=' => 'will@flourishlib.com'));
		$this->assertEquals(
			array(2, 3, 4),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionNotEqualType3()
	{
		$set = fRecordSet::build('User', array('email_address<>' => 'john@smith.com'));
		$this->assertEquals(
			array(1, 3, 4),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionLike()
	{
		$set = fRecordSet::build('User', array('email_address~' => 'EXAMPLE'));
		$this->assertEquals(
			array(3, 4),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionEmptyLike()
	{
		$set = fRecordSet::build('User', array('user_id|email_address~' => ''));
		$this->assertEquals(
			array(1, 2, 3, 4),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionEmptyLike2()
	{
		$set = fRecordSet::build('User', array('user_id|email_address~' => array()));
		$this->assertEquals(
			array(1, 2, 3, 4),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionNotLike()
	{
		$set = fRecordSet::build('User', array('email_address!~' => 'EXAMPLE'));
		$this->assertEquals(
			array(1, 2),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionLessThan()
	{
		$set = fRecordSet::build('Song', array('track_number<' => 2));
		$this->assertEquals(
			array(1, 11, 27),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionLessThanOrEqual()
	{
		$set = fRecordSet::build('Song', array('track_number<=' => 1));
		$this->assertEquals(
			array(1, 11, 27),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionGreaterThan()
	{
		$set = fRecordSet::build('Song', array('track_number>' => 13));
		$this->assertEquals(
			array(24, 25, 26),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionGreaterThanOrEqual()
	{
		$set = fRecordSet::build('Song', array('track_number>=' => 13));
		$this->assertEquals(
			array(23, 24, 25, 26, 39),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionEqualMultiValue()
	{
		$set = fRecordSet::build('User', array('email_address=' => array('john@smith.com', 'will@flourishlib.com')));
		$this->assertEquals(
			array(1, 2),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionNotEqualMultiValue()
	{
		$set = fRecordSet::build('User', array('email_address!' => array('john@smith.com', NULL)));
		$this->assertEquals(
			array(1, 3, 4),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionNotEqualMultiValueType2()
	{
		$set = fRecordSet::build('User', array('email_address!=' => array('john@smith.com', 'will@flourishlib.com')));
		$this->assertEquals(
			array(3, 4),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionNotEqualMultiValueType3()
	{
		$set = fRecordSet::build('User', array('email_address<>' => array('foo@example.com', 'john@smith.com', 'will@flourishlib.com')));
		$this->assertEquals(
			array(3),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionLikeMultiValue()
	{
		$set = fRecordSet::build('User', array('email_address~' => array('example', 'flourish')));
		$this->assertEquals(
			array(1, 3, 4),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionAndLike()
	{
		$set = fRecordSet::build('User', array('email_address&~' => array('example', 'bar')));
		$this->assertEquals(
			array(3),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionNotLikeMultiValue()
	{
		$set = fRecordSet::build('User', array('email_address!~' => array('EXAMPLE', 'flourish')));
		$this->assertEquals(
			array(2),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionOrConditions()
	{
		$set = fRecordSet::build('User', array('last_name=|email_address!=' => array('Bond', 'bar@example.com')));
		$this->assertEquals(
			array(1, 2, 4),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionIntersect()
	{
		$set = fRecordSet::build('Event', array('start_date|end_date><' => array('2007-12-31', '2008-02-05')));
		$this->assertEquals(
			array(1, 2, 3, 5, 7, 8, 9),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionIntersectNoSecondValue()
	{
		$set = fRecordSet::build('Event', array('start_date|end_date><' => array('2008-02-02', NULL)));
		$this->assertEquals(
			array(2, 3, 5, 9),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionMultiColumnLike()
	{
		$set = fRecordSet::build('User', array('last_name|email_address~' => 'bar'));
		$this->assertEquals(
			array(3, 4),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionMultiColumnSearchStringLike()
	{
		$set = fRecordSet::build('User', array('last_name|email_address~' => '.com b'));
		$this->assertEquals(
			array(1, 3, 4),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionMultiColumnMultiValueLike()
	{
		$set = fRecordSet::build('User', array('last_name|email_address~' => array('.com', 'b')));
		$this->assertEquals(
			array(1, 3, 4),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionMultiple()
	{
		$set = fRecordSet::build(
			'Event',
			array(
				'start_date|end_date><' => array('2008-02-02', NULL),
				'title~'                => 'th'
			)
		);
		$this->assertEquals(
			array(3, 5, 9),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionAggregateFunctionCount()
	{
		$set = fRecordSet::build('User', array(sprintf('count(%s{owns_on_cd}.album_id)=', fORM::tablize('Album')) => 3));
		$this->assertEquals(
			array(1),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionAggregateFunctionSum()
	{
		$set = fRecordSet::build('User', array(sprintf('sum(%s{owns_on_cd}.album_id)=', fORM::tablize('Album')) => 6));
		$this->assertEquals(
			array(1),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionAggregateFunctionMin()
	{
		$set = fRecordSet::build('User', array(sprintf('min(%s{owns_on_cd}.album_id)=', fORM::tablize('Album')) => 1));
		$this->assertEquals(
			array(1, 2, 4),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionAggregateFunctionMax()
	{
		$set = fRecordSet::build('User', array(sprintf('max(%s{owns_on_cd}.album_id)=', fORM::tablize('Album')) => 3));
		$this->assertEquals(
			array(1, 3),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionAggregateFunctionAvg()
	{
		$set = fRecordSet::build('User', array(sprintf('avg(%s{owns_on_cd}.album_id)=', fORM::tablize('Album')) => 2));
		$this->assertEquals(
			array(1),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionAggregateAndRegular()
	{
		$set = fRecordSet::build(
			'User',
			array(
				sprintf('max(%s{owns_on_cd}.album_id)=', fORM::tablize('Album')) => 3,
				'first_name=' => 'Will'
			)
		);
		$this->assertEquals(
			array(1),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionColumnCompareEqual()
	{
		$set = fRecordSet::build('User', array('user_id=:' => sprintf('%s{users_groups}.group_id', fORM::tablize('Group'))));
		$this->assertEquals(
			array(1, 2),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionColumnCompareNotEqual()
	{
		$set = fRecordSet::build('Event', array('start_date!:' => 'end_date'));
		$this->assertEquals(
			array(2, 3, 4, 5, 6, 7),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionColumnCompareNotEqual2()
	{
		$set = fRecordSet::build('Event', array('start_date!=:' => 'end_date'));
		$this->assertEquals(
			array(2, 3, 4, 5, 6, 7),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionColumnCompareNotEqual3()
	{
		$set = fRecordSet::build('Event', array('start_date<>:' => 'end_date'));
		$this->assertEquals(
			array(2, 3, 4, 5, 6, 7),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionColumnCompareLessThan()
	{
		$set = fRecordSet::build('Album', array('album_id<:' => 'top_albums.position'));
		$this->assertEquals(
			array(2, 3),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionColumnCompareLessThanEqual()
	{
		$set = fRecordSet::build('Album', array('album_id<=:' => 'top_albums.position'));
		$this->assertEquals(
			array(1, 2, 3),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionColumnCompareGreaterThan()
	{
		$set = fRecordSet::build('Album', array('album_id>:' => 'top_albums.position'));
		$this->assertEquals(
			array(4, 5, 6),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionColumnCompareGreaterThanEqual()
	{
		$set = fRecordSet::build('Album', array('album_id>=:' => 'top_albums.position'));
		$this->assertEquals(
			array(1, 4, 5, 6),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionColumnCompareAggregate()
	{
		$set = fRecordSet::build(
			'User',
			array(
				sprintf('count(%s{users_groups}.group_id)=:', fORM::tablize('Group')) =>
				sprintf('count(%s{group_founder}.group_id)', fORM::tablize('Group'))
			)
		);
		$this->assertEquals(
			array(2),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithWhereConditionInvalidColumn()
	{
		$this->setExpectedException('fProgrammerException');
		$set = fRecordSet::build('User', array('email=' => 'will@flourishlib.com'));
	}
	
	public function testTally()
	{
		$num = fRecordSet::tally('User');
		$this->assertEquals(
			4,
			$num
		);
	}
	
	public function testTallyWithWhereCondition()
	{
		$num = fRecordSet::tally('User', array('email_address=' => 'will@flourishlib.com'));
		$this->assertEquals(
			1,
			$num
		);
	}
	
	public function testTallyWithWhereConditionColumnConcat()
	{
		$num = fRecordSet::tally('User', array('first_name||last_name=' => 'WillBond'));
		$this->assertEquals(
			1,
			$num
		);
	}
	
	public function testTallyWithWhereConditionColumnConcatWithString()
	{
		$num = fRecordSet::tally('User', array("first_name||' '||last_name=" => 'Will Bond'));
		$this->assertEquals(
			1,
			$num
		);
	}
	
	public function testTallyWithWhereConditionFullyQualified()
	{
		$num = fRecordSet::tally('User', array(sprintf('%s.email_address=', fORM::tablize('User')) => 'will@flourishlib.com'));
		$this->assertEquals(
			1,
			$num
		);
	}
	
	public function testTallyWithWhereConditionRelatedTableManyToManyRoute()
	{
		$num = fRecordSet::tally('User', array(sprintf('%s{users_groups}.name=', fORM::tablize('Group')) => 'Musicians'));
		$this->assertEquals(
			2,
			$num
		);
	}
	
	public function testTallyWithWhereConditionRelatedTableOneToManyRoute()
	{
		$num = fRecordSet::tally('User', array(sprintf('%s{group_leader}.name=', fORM::tablize('Group')) => 'Music Lovers'));
		$this->assertEquals(
			1,
			$num
		);
	}
	
	public function testTallyWithWhereConditionRelatedTableOneToManyRoute2()
	{
		$num = fRecordSet::tally('User', array(sprintf('%s{group_founder}.name=', fORM::tablize('Group')) => 'Music Lovers'));
		$this->assertEquals(
			1,
			$num
		);
	}
	
	public function testTallyWithWhereConditionOnceRemovedRelatedTable()
	{
		$num = fRecordSet::tally('User', array(sprintf('%s{owns_on_cd}=>songs.track_number>', fORM::tablize('Album')) => 13));
		$this->assertEquals(
			2,
			$num
		);
	}
	
	public function testTallyWithWhereConditionNotEqual()
	{
		$num = fRecordSet::tally('User', array('email_address!' => NULL));
		$this->assertEquals(
			4,
			$num
		);
	}
	
	public function testTallyWithWhereConditionNotEqualType2()
	{
		$num = fRecordSet::tally('User', array('email_address!=' => 'will@flourishlib.com'));
		$this->assertEquals(
			3,
			$num
		);
	}
	
	public function testTallyWithWhereConditionNotEqualType3()
	{
		$num = fRecordSet::tally('User', array('email_address<>' => 'john@smith.com'));
		$this->assertEquals(
			3,
			$num
		);
	}
	
	public function testTallyWithWhereConditionLike()
	{
		$num = fRecordSet::tally('User', array('email_address~' => 'EXAMPLE'));
		$this->assertEquals(
			2,
			$num
		);
	}
	
	public function testTallyWithWhereConditionNotLike()
	{
		$num = fRecordSet::tally('User', array('email_address!~' => 'EXAMPLE'));
		$this->assertEquals(
			2,
			$num
		);
	}
	
	public function testTallyWithWhereConditionLessThan()
	{
		$num = fRecordSet::tally('Song', array('track_number<' => 2));
		$this->assertEquals(
			3,
			$num
		);
	}
	
	public function testTallyWithWhereConditionLessThanOrEqual()
	{
		$num = fRecordSet::tally('Song', array('track_number<=' => 1));
		$this->assertEquals(
			3,
			$num
		);
	}
	
	public function testTallyWithWhereConditionGreaterThan()
	{
		$num = fRecordSet::tally('Song', array('track_number>' => 13));
		$this->assertEquals(
			3,
			$num
		);
	}
	
	public function testTallyWithWhereConditionGreaterThanOrEqual()
	{
		$num = fRecordSet::tally('Song', array('track_number>=' => 13));
		$this->assertEquals(
			5,
			$num
		);
	}
	
	public function testTallyWithWhereConditionEqualMultiValue()
	{
		$num = fRecordSet::tally('User', array('email_address=' => array('john@smith.com', 'will@flourishlib.com')));
		$this->assertEquals(
			2,
			$num
		);
	}
	
	public function testTallyWithWhereConditionNotEqualMultiValue()
	{
		$num = fRecordSet::tally('User', array('email_address!' => array('john@smith.com', NULL)));
		$this->assertEquals(
			3,
			$num
		);
	}
	
	public function testTallyWithWhereConditionNotEqualMultiValueType2()
	{
		$num = fRecordSet::tally('User', array('email_address!=' => array('john@smith.com', 'will@flourishlib.com')));
		$this->assertEquals(
			2,
			$num
		);
	}
	
	public function testTallyWithWhereConditionNotEqualMultiValueType3()
	{
		$num = fRecordSet::tally('User', array('email_address<>' => array('foo@example.com', 'john@smith.com', 'will@flourishlib.com')));
		$this->assertEquals(
			1,
			$num
		);
	}
	
	public function testTallyWithWhereConditionLikeMultiValue()
	{
		$num = fRecordSet::tally('User', array('email_address~' => array('example', 'flourish')));
		$this->assertEquals(
			3,
			$num
		);
	}
	
	public function testTallyWithWhereConditionAndLike()
	{
		$num = fRecordSet::tally('User', array('email_address&~' => array('example', 'bar')));
		$this->assertEquals(
			1,
			$num
		);
	}
	
	public function testTallyWithWhereConditionNotLikeMultiValue()
	{
		$num = fRecordSet::tally('User', array('email_address!~' => array('EXAMPLE', 'flourish')));
		$this->assertEquals(
			1,
			$num
		);
	}
	
	public function testTallyWithWhereConditionOrConditions()
	{
		$num = fRecordSet::tally('User', array('last_name=|email_address!=' => array('Bond', 'bar@example.com')));
		$this->assertEquals(
			3,
			$num
		);
	}
	
	public function testTallyWithWhereConditionIntersect()
	{
		$num = fRecordSet::tally('Event', array('start_date|end_date><' => array('2007-12-31', '2008-02-05')));
		$this->assertEquals(
			7,
			$num
		);
	}
	
	public function testTallyWithWhereConditionIntersectNoSecondValue()
	{
		$num = fRecordSet::tally('Event', array('start_date|end_date><' => array('2008-02-02', NULL)));
		$this->assertEquals(
			4,
			$num
		);
	}
	
	public function testTallyWithWhereConditionMultiColumnLike()
	{
		$num = fRecordSet::tally('User', array('last_name|email_address~' => 'bar'));
		$this->assertEquals(
			2,
			$num
		);
	}
	
	public function testTallyWithWhereConditionMultiColumnSearchStringLike()
	{
		$num = fRecordSet::tally('User', array('last_name|email_address~' => '.com b'));
		$this->assertEquals(
			3,
			$num
		);
	}
	
	public function testTallyWithWhereConditionMultiColumnMultiValueLike()
	{
		$num = fRecordSet::tally('User', array('last_name|email_address~' => array('.com', 'b')));
		$this->assertEquals(
			3,
			$num
		);
	}
	
	public function testTallyWithWhereConditionMultiple()
	{
		$num = fRecordSet::tally(
			'Event',
			array(
				'start_date|end_date><' => array('2008-02-02', NULL),
				'title~'                => 'th'
			)
		);
		$this->assertEquals(
			3,
			$num
		);
	}
	
	public function testTallyWithWhereConditionAggregateFunctionCount()
	{
		$num = fRecordSet::tally('User', array(sprintf('count(%s{owns_on_cd}.album_id)=', fORM::tablize('Album')) => 3));
		$this->assertEquals(
			1,
			$num
		);
	}
	
	public function testTallyWithWhereConditionAggregateFunctionSum()
	{
		$num = fRecordSet::tally('User', array(sprintf('sum(%s{owns_on_cd}.album_id)=', fORM::tablize('Album')) => 6));
		$this->assertEquals(
			1,
			$num
		);
	}
	
	public function testTallyWithWhereConditionAggregateFunctionMin()
	{
		$num = fRecordSet::tally('User', array(sprintf('min(%s{owns_on_cd}.album_id)=', fORM::tablize('Album')) => 1));
		$this->assertEquals(
			3,
			$num
		);
	}
	
	public function testTallyWithWhereConditionAggregateFunctionMax()
	{
		$num = fRecordSet::tally('User', array(sprintf('max(%s{owns_on_cd}.album_id)=', fORM::tablize('Album')) => 3));
		$this->assertEquals(
			2,
			$num
		);
	}
	
	public function testTallyWithWhereConditionAggregateFunctionAvg()
	{
		$num = fRecordSet::tally('User', array(sprintf('avg(%s{owns_on_cd}.album_id)=', fORM::tablize('Album')) => 2));
		$this->assertEquals(
			1,
			$num
		);
	}
	
	public function testTallyWithWhereConditionAggregateAndRegular()
	{
		$num = fRecordSet::tally(
			'User',
			array(
				sprintf('max(%s{owns_on_cd}.album_id)=', fORM::tablize('Album')) => 3,
				'first_name=' => 'Will'
			)
		);
		$this->assertEquals(
			1,
			$num
		);
	}
	
	public function testTallyWithWhereConditionColumnCompareEqual()
	{
		$num = fRecordSet::tally('User', array('user_id=:' => sprintf('%s{users_groups}.group_id', fORM::tablize('Group'))));
		$this->assertEquals(
			2,
			$num
		);
	}
	
	public function testTallyWithWhereConditionColumnCompareNotEqual()
	{
		$num = fRecordSet::tally('Event', array('start_date!:' => 'end_date'));
		$this->assertEquals(
			6,
			$num
		);
	}
	
	public function testTallyWithWhereConditionColumnCompareNotEqual2()
	{
		$num = fRecordSet::tally('Event', array('start_date!=:' => 'end_date'));
		$this->assertEquals(
			6,
			$num
		);
	}
	
	public function testTallyWithWhereConditionColumnCompareNotEqual3()
	{
		$num = fRecordSet::tally('Event', array('start_date<>:' => 'end_date'));
		$this->assertEquals(
			6,
			$num
		);
	}
	
	public function testTallyWithWhereConditionColumnCompareLessThan()
	{
		$num = fRecordSet::tally('Album', array('album_id<:' => 'top_albums.position'));
		$this->assertEquals(
			2,
			$num
		);
	}
	
	public function testTallyWithWhereConditionColumnCompareLessThanEqual()
	{
		$num = fRecordSet::tally('Album', array('album_id<=:' => 'top_albums.position'));
		$this->assertEquals(
			3,
			$num
		);
	}
	
	public function testTallyWithWhereConditionColumnCompareGreaterThan()
	{
		$num = fRecordSet::tally('Album', array('album_id>:' => 'top_albums.position'));
		$this->assertEquals(
			3,
			$num
		);
	}
	
	public function testTallyWithWhereConditionColumnCompareGreaterThanEqual()
	{
		$num = fRecordSet::tally('Album', array('album_id>=:' => 'top_albums.position'));
		$this->assertEquals(
			4,
			$num
		);
	}
	
	public function testTallyWithWhereConditionColumnCompareAggregate()
	{
		$num = fRecordSet::tally(
			'User',
			array(
				sprintf('count(%s{users_groups}.group_id)=:', fORM::tablize('Group')) =>
				sprintf('count(%s{group_founder}.group_id)', fORM::tablize('Group'))
			)
		);
		$this->assertEquals(
			1,
			$num
		);
	}
	
	public function testTallyWithWhereConditionInvalidColumn()
	{
		$this->setExpectedException('fProgrammerException');
		$set = fRecordSet::tally('User', array('email=' => 'will@flourishlib.com'));
	}
	
	public function testBuildWithOrderBy()
	{
		$set = fRecordSet::build('User', NULL, array('first_name' => 'asc'));
		$this->assertEquals(
			array(3, 4, 2, 1),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithNonAggregateOrderBy()
	{
		$set = fRecordSet::build(
			'User',
			NULL,
			array(
				sprintf('%s{users_groups}.name', fORM::tablize('Group')) => 'asc',
				'user_id'                   => 'asc'
			)
		);
		$this->assertEquals(
			// Postgres has a different collation than the others
			DB_TYPE == 'postgresql' ? array(1, 2, 3, 4) : array(3, 4, 1, 2),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithNonAggregateOrderBy2()
	{
		$set = fRecordSet::build(
			'Album',
			array(sprintf('%s.name~', fORM::tablize('Artist')) => 'e'),
			array(
				sprintf('%s.name', fORM::tablize('Artist')) => 'asc',
				'name'         => 'asc'
			)
		);
		$this->assertEquals(
			array(5, 6, 4, 7, 3, 2, 1),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithLimit()
	{
		$set = fRecordSet::build('User', NULL, NULL, 2);
		$this->assertEquals(
			array(1, 2),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithLimitAndPage()
	{
		$set = fRecordSet::build('User', NULL, NULL, 2, 2);
		$this->assertEquals(
			array(3, 4),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildWithLimitGetLimit()
	{
		$set = fRecordSet::build('User', NULL, NULL, 2);
		$this->assertEquals(
			2,
			$set->getLimit()
		);
	}
	
	public function testBuildWithoutLimitGetLimit()
	{
		$set = fRecordSet::build('User');
		$this->assertEquals(
			NULL,
			$set->getLimit()
		);
	}
	
	public function testBuildWithLimitAndPageGetPage()
	{
		$set = fRecordSet::build('User', NULL, NULL, 2, 2);
		$this->assertEquals(
			2,
			$set->getPage()
		);
	}
	
	public function testBuildWithLimitAndPageGetPages()
	{
		$set = fRecordSet::build('User', NULL, NULL, 2);
		$this->assertEquals(
			2,
			$set->getPages()
		);
	}
	
	public function testBuildFailureIncorrectClass()
	{
		$this->setExpectedException('fProgrammerException');
		$set = fRecordSet::build('Exception');
	}
	
	public function testBuildFromArray()
	{
		$set = fRecordSet::buildFromArray('User', array(new User(1), new User(4)));
		$this->assertEquals(
			array(1, 4),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildFromArrayTotalLimitPage()
	{
		$set = fRecordSet::buildFromArray('User', array(new User(1), new User(4)), 4, 2, 1);
		$this->assertEquals(
			array(1, 4),
			$set->getPrimaryKeys()
		);
		
		$this->assertEquals(
			2,
			$set->getLimit()
		);
		$this->assertEquals(
			1,
			$set->getPage()
		);
		$this->assertEquals(
			2,
			$set->getPages()
		);
	}
	
	public function testBuildFromArrayEmpty()
	{
		$set = fRecordSet::buildFromArray('User', array());
		$this->assertEquals(
			array(),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildFromArrayMixed()
	{
		$set = fRecordSet::buildFromArray(array('User', 'Song'), array(new User(1), new User(4), new Song(1)));
		$this->assertEquals(
			3,
			$set->count()
		);
	}
	
	public function testBuildFromArrayFailureNotArray()
	{
		$this->setExpectedException('fProgrammerException');
		$set = fRecordSet::buildFromArray('User', sprintf("SELECT * FROM %s", fORM::tablize('User')));
	}
	
	public function testBuildFromArrayFailureIncorrectClass()
	{
		$this->setExpectedException('fProgrammerException');
		$set = fRecordSet::buildFromArray('Exception', array());
	}
	
	public function testBuildFromSQL()
	{
		$set = fRecordSet::buildFromSQL('User', sprintf("SELECT * FROM %s", fORM::tablize('User')));
		$this->assertEquals(
			array(1, 2, 3, 4),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildFromSQLEscapeLimitPage()
	{
		$set = fRecordSet::buildFromSQL(
			'User',
			array("SELECT * FROM " . fORM::tablize('User') . " WHERE user_id < %i LIMIT %i", 3, 1),
			array("SELECT count(*) FROM " . fORM::tablize('User') . " WHERE user_id < %i", 3),
			1,
			1
		);
		$this->assertEquals(
			array(1),
			$set->getPrimaryKeys()
		);
		$this->assertEquals(
			1,
			$set->getLimit()
		);
		$this->assertEquals(
			1,
			$set->getPage()
		);
		$this->assertEquals(
			2,
			$set->getPages()
		);
	}
	
	public function testBuildFromSQLDistinct()
	{
		$set = fRecordSet::buildFromSQL(
			'User',
			sprintf(
				"SELECT DISTINCT %s.* FROM %s ORDER BY %s.user_id ASC",
				fORM::tablize('User'),
				fORM::tablize('User'),
				fORM::tablize('User')
			)
		);
		$this->assertEquals(
			array(1, 2, 3, 4),
			$set->getPrimaryKeys()
		);
	}
	
	public function testBuildFromSQLNonLimitedCount()
	{
		$set = fRecordSet::buildFromSQL(
			'User',
			sprintf("SELECT %s.* FROM %s LIMIT 2", fORM::tablize('User'), fORM::tablize('User')),
			sprintf("SELECT count(*) FROM %s", fORM::tablize('User'))
		);
		$this->assertEquals(
			array(1, 2),
			$set->getPrimaryKeys()
		);
		$this->assertEquals(
			4,
			$set->count(TRUE)
		);
	}
	
	public function testBuildFromCallCreateShortcut()
	{
		$set = fRecordSet::build('Song', array('track_number=' => 1));
		$albums = $set->buildAlbums();
		$this->assertEquals(
			TRUE,
			$albums instanceof fRecordSet
		);
		$this->assertEquals(
			3,
			$albums->count()
		);
	}
	
	public function testBuildFromCall()
	{
		$set = fRecordSet::build('Song', array('track_number=' => 1));
		$albums = $set->buildFromCall('createAlbum');
		$this->assertEquals(
			TRUE,
			$albums instanceof fRecordSet
		);
		$this->assertEquals(
			3,
			$albums->count()
		);
	}
	
	public function testBuildFromCall2()
	{
		$set          = fRecordSet::build('User');
		$user_details = $set->buildFromCall('createUserDetail');
		$this->assertEquals(
			TRUE,
			$user_details instanceof fRecordSet
		);
		$this->assertEquals(
			4,
			$user_details->count()
		);
	}
	
	public function testBuildFromMap()
	{
		$set = fRecordSet::build('Song', array('track_number=' => 1));
		$albums = $set->buildFromMap('Album', '{record}::getAlbumId');
		$this->assertEquals(
			TRUE,
			$albums instanceof fRecordSet
		);
		$this->assertEquals(
			3,
			$albums->count()
		);
	}
	
	public function testContains()
	{
		$set = fRecordSet::build('User', array('user_id>' => 2));
		$this->assertEquals(
			TRUE,
			$set->contains(new User(3))
		);
		$this->assertEquals(
			FALSE,
			$set->contains(new User(2))
		);
	}
	
	public function testContainsDifferentClass()
	{
		$set = fRecordSet::build('User', array('user_id>' => 2));
		$this->assertEquals(
			FALSE,
			$set->contains(new Song(3))
		);
	}
	
	public function testDiff()
	{
		$set = fRecordSet::build('User', array('user_id>' => 2));
		$this->assertEquals(
			array(4),
			$set->diff(new User(3))->getPrimaryKeys()
		);
	}
	
	public function testDiffDifferentClass()
	{
		$set = fRecordSet::build('User', array('user_id>' => 2));
		$this->assertEquals(
			array(3, 4),
			$set->diff(new Song(3))->getPrimaryKeys()
		);
	}
	
	public function testFilter()
	{
		$set = fRecordSet::build('User');
		$this->assertEquals(
			array(1),
			$set->filter(array('getEmailAddress=' => 'will@flourishlib.com'))->getPrimaryKeys()
		);
	}
	
	public function testFilterNotEqual()
	{
		$set = fRecordSet::build('User');
		$set = $set->filter(array('getEmailAddress!' => NULL));
		$this->assertEquals(
			array(1, 2, 3, 4),
			$set->getPrimaryKeys()
		);
	}
	
	public function testFilterNotEqualType2()
	{
		$set = fRecordSet::build('User');
		$set = $set->filter(array('getEmailAddress!=' => 'will@flourishlib.com'));
		$this->assertEquals(
			array(2, 3, 4),
			$set->getPrimaryKeys()
		);
	}
	
	public function testFilterNotEqualType3()
	{
		$set = fRecordSet::build('User');
		$set = $set->filter(array('getEmailAddress<>' => 'john@smith.com'));
		$this->assertEquals(
			array(1, 3, 4),
			$set->getPrimaryKeys()
		);
	}
	
	public function testFilterLike()
	{
		$set = fRecordSet::build('User');
		$set = $set->filter(array('getEmailAddress~' => 'EXAMPLE'));
		$this->assertEquals(
			array(3, 4),
			$set->getPrimaryKeys()
		);
	}
	
	public function testFilterNotLike()
	{
		$set = fRecordSet::build('User');
		$set = $set->filter(array('getEmailAddress!~' => 'EXAMPLE'));
		$this->assertEquals(
			array(1, 2),
			$set->getPrimaryKeys()
		);
	}
	
	public function testFilterLessThan()
	{
		$set = fRecordSet::build('Song');
		$set = $set->filter(array('getTrackNumber<' => 2));
		$this->assertEquals(
			array(1, 11, 27),
			$set->getPrimaryKeys()
		);
	}
	
	public function testFilterLessThanOrEqual()
	{
		$set = fRecordSet::build('Song');
		$set = $set->filter(array('getTrackNumber<=' => 1));
		$this->assertEquals(
			array(1, 11, 27),
			$set->getPrimaryKeys()
		);
	}
	
	public function testFilterGreaterThan()
	{
		$set = fRecordSet::build('Song');
		$set = $set->filter(array('getTrackNumber>' => 13));
		$this->assertEquals(
			array(24, 25, 26),
			$set->getPrimaryKeys()
		);
	}
	
	public function testFilterGreaterThanOrEqual()
	{
		$set = fRecordSet::build('Song');
		$set = $set->filter(array('getTrackNumber>=' => 13));
		$this->assertEquals(
			array(23, 24, 25, 26, 39),
			$set->getPrimaryKeys()
		);
	}
	
	public function testFilterEqualMultiValue()
	{
		$set = fRecordSet::build('User');
		$set = $set->filter(array('getEmailAddress=' => array('john@smith.com', 'will@flourishlib.com')));
		$this->assertEquals(
			array(1, 2),
			$set->getPrimaryKeys()
		);
	}
	
	public function testFilterNotEqualMultiValue()
	{
		$set = fRecordSet::build('User');
		$set = $set->filter(array('getEmailAddress!' => array('john@smith.com', NULL)));
		$this->assertEquals(
			array(1, 3, 4),
			$set->getPrimaryKeys()
		);
	}
	
	public function testFilterNotEqualMultiValueType2()
	{
		$set = fRecordSet::build('User');
		$set = $set->filter(array('getEmailAddress!=' => array('john@smith.com', 'will@flourishlib.com')));
		$this->assertEquals(
			array(3, 4),
			$set->getPrimaryKeys()
		);
	}
	
	public function testFilterNotEqualMultiValueType3()
	{
		$set = fRecordSet::build('User');
		$set = $set->filter(array('getEmailAddress<>' => array('foo@example.com', 'john@smith.com', 'will@flourishlib.com')));
		$this->assertEquals(
			array(3),
			$set->getPrimaryKeys()
		);
	}
	
	public function testFilterLikeMultiValue()
	{
		$set = fRecordSet::build('User');
		$set = $set->filter(array('getEmailAddress~' => array('example', 'flourish')));
		$this->assertEquals(
			array(1, 3, 4),
			$set->getPrimaryKeys()
		);
	}
	
	public function testFilterAndLike()
	{
		$set = fRecordSet::build('User');
		$set = $set->filter(array('getEmailAddress&~' => array('example', 'bar')));
		$this->assertEquals(
			array(3),
			$set->getPrimaryKeys()
		);
	}
	
	public function testFilterNotLikeMultiValue()
	{
		$set = fRecordSet::build('User');
		$set = $set->filter(array('getEmailAddress!~' => array('EXAMPLE', 'flourish')));
		$this->assertEquals(
			array(2),
			$set->getPrimaryKeys()
		);
	}
	
	public function testFilterOrConditions()
	{
		$set = fRecordSet::build('User');
		$set = $set->filter(array('getLastName=|getEmailAddress!=' => array('Bond', 'bar@example.com')));
		$this->assertEquals(
			array(1, 2, 4),
			$set->getPrimaryKeys()
		);
	}
	
	public function testFilterIntersect()
	{
		$set = fRecordSet::build('Event');
		$set = $set->filter(array('getStartDate|getEndDate><' => array('2007-12-31', '2008-02-05')));
		$this->assertEquals(
			array(1, 2, 3, 5, 7, 8, 9),
			$set->getPrimaryKeys()
		);
	}
	
	public function testFilterIntersectNoSecondValue()
	{
		$set = fRecordSet::build('Event');
		$set = $set->filter(array('getStartDate|getEndDate><' => array(DB_TYPE == 'mssql' ? '2008-02-02 00:00:00' : '2008-02-02', NULL)));
		$this->assertEquals(
			array(2, 3, 5, 9),
			$set->getPrimaryKeys()
		);
	}
	
	public function testFilterMultiColumnLike()
	{
		$set = fRecordSet::build('User');
		$set = $set->filter(array('getLastName|getEmailAddress~' => 'bar'));
		$this->assertEquals(
			array(3, 4),
			$set->getPrimaryKeys()
		);
	}
	
	public function testFilterMultiColumnSearchStringLike()
	{
		$set = fRecordSet::build('User');
		$set = $set->filter(array('getLastName|getEmailAddress~' => '.com b'));
		$this->assertEquals(
			array(1, 3, 4),
			$set->getPrimaryKeys()
		);
	}
	
	public function testFilterMultiColumnMultiValueLike()
	{
		$set = fRecordSet::build('User');
		$set = $set->filter(array('getLastName|getEmailAddress~' => array('.com', 'b')));
		$this->assertEquals(
			array(1, 3, 4),
			$set->getPrimaryKeys()
		);
	}
	
	public function testIntersect()
	{
		$set = fRecordSet::build('User');
		$this->assertEquals(
			array(3),
			$set->intersect(new User(3))->getPrimaryKeys()
		);
	}
	
	public function testIntersectDifferentClass()
	{
		$set = fRecordSet::build('User');
		$this->assertEquals(
			array(),
			$set->intersect(new Song(3))->getPrimaryKeys()
		);
	}
	
	public function testMap()
	{
		$set = fRecordSet::build('User');
		$this->assertEquals(
			array('will', 'john', 'bar', 'foo'),
			$set->map('fUTF8::lower', '{record}::getFirstName')
		);
	}
	
	public function testMapMultipleParams()
	{
		$set = fRecordSet::build('User');
		$this->assertEquals(
			array('Will', 'John', 'Bar', 'Foo'),
			$set->map('htmlentities', '{record}::getFirstName', ENT_COMPAT, 'UTF-8')
		);
	}
	
	public function testMerge()
	{
		$set = fRecordSet::build('User', array('user_id>' => 2));
		$this->assertEquals(
			array(3, 4, 1, 2),
			$set->merge(
				fRecordSet::buildFromArray(
					'User',
					array(new user(1), new User(2))
				)
			)->getPrimaryKeys()
		);
	}
	
	public function testMergeSingle()
	{
		$set = fRecordSet::build('User', array('user_id>' => 2));
		$this->assertEquals(
			array(3, 4, 1),
			$set->merge(new user(1))->getPrimaryKeys()
		);
	}
	
	public function testReduce()
	{
		$set = fRecordSet::build('User');
		$this->assertEquals(
			6,
			$set->reduce('_tally', 0)
		);
	}
	
	public function testSlice()
	{
		$set = fRecordSet::build('User');
		$this->assertEquals(
			array(1, 2),
			$set->slice(0, 2)->getPrimaryKeys()
		);
	}
	
	public function testSliceLimit()
	{
		$set = fRecordSet::build('User');
		$set = $set->slice(2, 2, TRUE);
		$this->assertEquals(
			array(3, 4),
			$set->getPrimaryKeys()
		);
		$this->assertEquals(
			2,
			$set->getLimit()
		);
		$this->assertEquals(
			2,
			$set->getPage()
		);
		$this->assertEquals(
			2,
			$set->getPages()
		);
	}
	
	public function testSplit()
	{
		$set = fRecordSet::build('Song', array('album_id=' => 1));
		$sets = $set->split(3);
		
		$this->assertEquals(
			array(
				array(1, 2, 3, 4),
				array(5, 6, 7, 8),
				array(9, 10)
			),
			array(
				$sets[0]->getPrimaryKeys(),
				$sets[1]->getPrimaryKeys(),
				$sets[2]->getPrimaryKeys()
			)
		);
	}
	
	public function testChunk()
	{
		$set = fRecordSet::build('Song', array('album_id=' => 1));
		$sets = $set->chunk(3);
		
		$this->assertEquals(
			array(
				array(1, 2, 3),
				array(4, 5, 6),
				array(7, 8, 9),
				array(10)
			),
			array(
				$sets[0]->getPrimaryKeys(),
				$sets[1]->getPrimaryKeys(),
				$sets[2]->getPrimaryKeys(),
				$sets[3]->getPrimaryKeys()
			)
		);
	}
	
	public function testSort()
	{
		$set = fRecordSet::build('User');
		$set->sort('getEmailAddress', 'asc');
		$this->assertEquals(
			array(3, 4, 2, 1),
			$set->getPrimaryKeys()
		);
	}
	
	public function testTossIfEmpty()
	{
		$this->setExpectedException('fEmptySetException');
		
		$set = fRecordSet::build('User', array('email_address=' => 'test@example.com'));
		$set->tossIfEmpty();
	}
	
	public function testUnique()
	{
		$set = fRecordSet::buildFromArray('User', array(new User(1, new User(2))));
		$set = $set->merge(new User(2));
		$set = $set->unique();
		$this->assertEquals(
			array(1, 2),
			$set->getPrimaryKeys()
		);
	}
}