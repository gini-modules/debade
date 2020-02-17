<?php

namespace Gini\DeBaDe\Queue;

class Database implements Driver
{
const PREPARE_SQL = <<<SQL
    CREATE TABLE IF NOT EXISTS _debade_queue (
      id bigint(20) NOT NULL AUTO_INCREMENT,
      hash varchar(32) NOT NULL DEFAULT '',
      ymlkey varchar(32) NOT NULL DEFAULT '',
      queue varchar(32) NOT NULL DEFAULT '',
      routing varchar(255) NOT NULL DEFAULT '',
      ctime TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      data text NOT NULL DEFAULT '',
      PRIMARY KEY (id),
      UNIQUE KEY _MIDX_0 (hash)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    CREATE TABLE IF NOT EXISTS _debade_queue_callback (
      hash varchar(32) NOT NULL DEFAULT '',
      callback varchar(25) NOT NULL DEFAULT '',
      status int(1) NOT NULL DEFAULT 0,
      ctime TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      UNIQUE KEY _MIDX_0 (hash, callback),
      CONSTRAINT `_debade_ck_hash` FOREIGN KEY (`hash`) REFERENCES `_debade_queue` (`hash`) ON DELETE CASCADE ON UPDATE NO ACTION
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    CREATE TRIGGER TRIG__DEBADE_QUEUE_CALLBACK_AFTER_UPDATE_V1 AFTER UPDATE ON _debade_queue_callback FOR EACH ROW DELETE FROM _debade_queue WHERE hash=new.hash AND cast(new.callback as signed)=(select count(*) FROM _debade_queue_callback where hash=new.hash and status=1);
SQL;

    private $_name;
    private $_queue;
    private $_db;
    private $_debade = null;

    public function log($level, $message, array $context = [])
    {
        $context['@name'] = $this->_name;
        \Gini\Logger::of('debade')->{$level}('Courier[{@name}] '.$message, $context);
    }

    public function getData($hash)
    {
        $qhash = $this->_db->quote($hash);
        $data = $this->_db->query("select data from _debade_queue where hash={$qhash}")->value();
        return @json_decode($data, true) ?: [];
    }

    // \Gini\Debade\Queue::of($key)->scribe($hash, $callback);
    // 声明开始执行消费逻辑
    public function scribe($hash, $callback)
    {
        $qhash = $this->_db->quote($hash);
        $qcallback = $this->_db->quote($callback);
        $query = $this->_db->query("insert into _debade_queue_callback (hash, callback) values({$qhash}, {$qcallback})");
        if ($query && $query->count()) return true;
        return false;
    }

    // \Gini\Debade\Queue::of($key)->ack($hash, $callback);
    // 消费逻辑执行完成
    public function ack($hash, $callback)
    {
        $qhash = $this->_db->quote($hash);
        $qcallback = $this->_db->quote($callback);
        $this->_db->query("update _debade_queue_callback set status=1 where hash={$qhash} and callback={$qcallback}");
        $finished = $this->_db->query("select count(*) from _debade_queue_callback where hash={$qhash} and status=1")->value();
        $total = explode('-', $callback)[0];
        if ($finished>0 and $total==$finished) {
            $this->_db->query("delete from _debade_queue where hash={$qhash}");
        }
    }

    // \Gini\Debade\Queue::of($key)->nack($hash, $callback);
    // 消费逻辑执行完成消费逻辑执行失败，需要等待重新出发
    public function nack($hash, $callback)
    {
        $qhash = $this->_db->quote($hash);
        $qcallback = $this->_db->quote($callback);
        $this->_db->query("delete from _debade_queue_callback where hash={$qhash} and callback={$qcallback} and status=0");
    }

    public function __construct($name, array $options = [])
    {
        try {
            $this->_name = $name;
            $this->_queue = (string)$options['queue'];
            $key = "---debade-queue-database-{$name}---";
            $defaultOPTs = \Gini\Config::get('debade.database');
            \Gini\Config::set("database.$key", [
                'dsn'=> $options['dsn'] ?: $defaultOPTs['dsn'],
                'username'=> $options['username'] ?: $defaultOPTs['username'],
                'password'=> $options['password'] ?: $defaultOPTs['password']
            ]);
            $this->_db = \Gini\Database::db($key);
        } catch (\Exception $e) {
            // DO NOTHING
            $this->log('error', 'error: {error}', ['error' => $e->getMessage()]);
        }
    }

    public function push($rmsg, $routing_key = null)
    {
        $data = [
            'ymlkey'=> $this->_name,
            'queue'=> $this->_queue,
            'routing'=> (string)$routing_key,
            'data'=> J($rmsg)
        ];
        $hash = $data['hash'] = md5(J($data));
        $value = $this->_db->quote($data);
        $sql = "INSERT INTO _debade_queue (ymlkey, queue, routing, data, hash) VALUES ({$value})";
        $this->_db->query($sql);
        $error = $this->_db->errorCode();
        if ($error=='42S02') {
            $this->_db->exec(self::PREPARE_SQL);
            $this->_db->query($sql);
        }

        \Gini\DeBaDe\Queue::of($this->_queue)->push([
            'debade::key'=> $this->_name,
            'debade::hash'=> $hash
        ], $routing_key);

        $this->log('debug', 'pushing message: {message}{routing}', ['message' => J($rmsg), 'routing' => $routing_key ? " R($routing_key)" : '']);
    }
}
