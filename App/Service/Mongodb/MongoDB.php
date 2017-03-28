<?php
namespace App\Service\Mongodb;

class MongoDb
{

    private $mongo;    //Mongodb����
    private $curr_db_name;
    private $curr_table_name;
    private $error;

    public function getInstance($mongo_server, $flag = array())
    {
        static $mongodb_arr;
        if (empty($flag['tag'])) {
            $flag['tag'] = 'default';
        }
        if (isset($flag['force']) && $flag['force'] == true) {
            $mongo = new MongoDb($mongo_server);
            if (empty($mongodb_arr[$flag['tag']])) {
                $mongodb_arr[$flag['tag']] = $mongo;
            }
            return $mongo;
        } else if (isset($mongodb_arr[$flag['tag']]) && is_resource($mongodb_arr[$flag['tag']])) {
            return $mongodb_arr[$flag['tag']];
        } else {
            $mongo = new MongoDb($mongo_server);
            $mongodb_arr[$flag['tag']] = $mongo;
            return $mongo;
        }
    }

    /**
     * ���캯��
     * ֧�ִ�����mongo_server(1.һ��������ʱ����������server 2.�Զ�����ѯ���ȷַ�����ͬserver)
     *
     * ������
     * $mongo_server:������ַ���-array("127.0.0.1:1111", "127.0.0.1:2222")-"127.0.0.1:1111"
     * $connect:��ʼ��mongo����ʱ�Ƿ����ӣ�Ĭ������
     * $auto_balance:�Ƿ��Զ������ؾ��⣬Ĭ����
     *
     * ����ֵ��
     * �ɹ���mongo object
     * ʧ�ܣ�false
     */
    public function __construct($mongo_server, $connect = true, $auto_balance = true)
    {
        if (is_array($mongo_server)) {
            $mongo_server_num = count($mongo_server);
            if ($mongo_server_num > 1 && $auto_balance) {
                $prior_server_num = rand(1, $mongo_server_num);
                $rand_keys = array_rand($mongo_server, $mongo_server_num);
                $mongo_server_str = $mongo_server[$prior_server_num - 1];
                foreach ($rand_keys as $key) {
                    if ($key != $prior_server_num - 1) {
                        $mongo_server_str .= ',' . $mongo_server[$key];
                    }
                }
            } else {
                $mongo_server_str = implode(',', $mongo_server);
            }
        } else {
            $mongo_server_str = $mongo_server;
        }
        try {
            $this->mongo = new \MongoClient($mongo_server, array('connect' => $connect));
        } catch (MongoConnectionException $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * ����mongodb server
     *
     * ��������
     *
     * ����ֵ��
     * �ɹ���true
     * ʧ�ܣ�false
     */
    public function connect()
    {
        try {
            $this->mongo->connect();
            return true;
        } catch (MongoConnectionException $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * select db
     *
     * ������$dbname
     *
     * ����ֵ����
     */
    public function selectDb($dbname)
    {
        $this->curr_db_name = $dbname;
        return $this;
    }

    /**
     * �����������������Ѵ��ڣ��򷵻ء�
     *
     * ������
     * $table_name:����
     * $index:����-array("id"=>1)-��id�ֶν�����������
     * $index_param:��������-�Ƿ�Ψһ������
     *
     * ����ֵ��
     * �ɹ���true
     * ʧ�ܣ�false
     */
    public function ensureIndex($table_name, $index, $index_param = array())
    {
        $dbname = $this->curr_db_name;
        $index_param['safe'] = 1;
        try {
            $this->mongo->$dbname->$table_name->ensureIndex($index, $index_param);
            return true;
        } catch (MongoCursorException $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * �����¼
     *
     * ������
     * $table_name:����
     * $record:��¼
     *
     * ����ֵ��
     * �ɹ���true
     * ʧ�ܣ�false
     */
    public function insert($table_name, $record)
    {
        $dbname = $this->curr_db_name;
        try {
            $this->mongo->$dbname->$table_name->insert($record, array('safe' => true));
            return true;
        } catch (MongoCursorException $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * ��ѯ��ļ�¼��
     *
     * ������
     * $table_name:����
     *
     * ����ֵ����ļ�¼��
     */
    public  function count($table_name,$condition=array())
    {
        $dbname = $this->curr_db_name;
        return $this->mongo->$dbname->$table_name->count($condition);
    }

    /**
     * ���¼�¼
     *
     * ������
     * $table_name:����
     * $condition:��������
     * $newdata:�µ����ݼ�¼
     * $options:����ѡ��-upsert/multiple
     *
     * ����ֵ��
     * �ɹ���true
     * ʧ�ܣ�false
     */
    public function update($table_name, $condition, $newdata, $options = array())
    {
        $dbname = $this->curr_db_name;
        $options['safe'] = 1;
        if (!isset($options['multiple'])) {
            $options['multiple'] = 0;
        }
        try {
            $this->mongo->$dbname->$table_name->update($condition, $newdata, $options);
            return true;
        } catch (MongoCursorException $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * ɾ����¼
     *
     * ������
     * $table_name:����
     * $condition:ɾ������
     * $options:ɾ��ѡ��-justOne
     *
     * ����ֵ��
     * �ɹ���true
     * ʧ�ܣ�false
     */
    public function remove($table_name, $condition, $options = array())
    {
        $dbname = $this->curr_db_name;
        $options['safe'] = 1;
        try {
            $this->mongo->$dbname->$table_name->remove($condition, $options);
            return true;
        } catch (MongoCursorException $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * ���Ҽ�¼
     *
     * ������
     * $table_name:����
     * $query_condition:�ֶβ�������
     * $result_condition:��ѯ�����������-limit/sort��
     * $fields:��ȡ�ֶ�
     *
     * ����ֵ��
     * �ɹ�����¼��
     * ʧ�ܣ�false
     */
    public function find($table_name, $query_condition, $result_condition = array(), $fields = array())
    {
        $dbname = $this->curr_db_name;
        $cursor = $this->mongo->$dbname->$table_name->find($query_condition, $fields);
        if (!empty($result_condition['start'])) {
            $cursor->skip($result_condition['start']);
        }
        if (!empty($result_condition['limit'])) {
            $cursor->limit($result_condition['limit']);
        }
        if (!empty($result_condition['sort'])) {
            $cursor->sort($result_condition['sort']);
        }
        $result = array();
        try {
            while ($cursor->hasNext()) {
                $result[] = $cursor->getNext();
            }
        } catch (MongoConnectionException $e) {
            $this->error = $e->getMessage();
            return false;
        } catch (MongoCursorTimeoutException $e) {
            $this->error = $e->getMessage();
            return false;
        }
        return $result;
    }

    /**
     * ����һ����¼
     *
     * ������
     * $table_name:����
     * $condition:��������
     * $fields:��ȡ�ֶ�
     *
     * ����ֵ��
     * �ɹ���һ����¼
     * ʧ�ܣ�false
     */
    public function findOne($table_name, $condition, $fields = array())
    {
        $dbname = $this->curr_db_name;
        return $this->mongo->$dbname->$table_name->findOne($condition, $fields);
    }

    /**
     * ��ȡ��ǰ������Ϣ
     *
     * ��������
     *
     * ����ֵ����ǰ������Ϣ
     */
    public function getError()
    {
        return $this->error;
    }

    /*** Mongodb��** examples:
     * $mongo = new HMongodb("127.0.0.1:11223");
     * $mongo->selectDb("test_db");
     * ��������
     * $mongo->ensureIndex("test_table", array("id"=>1), array('unique'=>true));
     * ��ȡ��ļ�¼
     * $mongo->count("test_table");
     * �����¼
     * $mongo->insert("test_table", array("id"=>2, "title"=>"asdqw"));
     * ���¼�¼
     * $mongo->update("test_table", array("id"=>1),array("id"=>1,"title"=>"bbb"));
     * ���¼�¼-����ʱ���£�������ʱ���-�൱��set
     * $mongo->update("test_table", array("id"=>1),array("id"=>1,"title"=>"bbb"),array("upsert"=>1));
     * ���Ҽ�¼
     * $mongo->find("c", array("title"=>"asdqw"), array("start"=>2,"limit"=>2,"sort"=>array("id"=>1)))
     * ����һ����¼
     * $mongo->findOne("$mongo->findOne("ttt", array("id"=>1))", array("id"=>1));
     * ɾ����¼
     * $mongo->remove("ttt", array("title"=>"bbb"));
     * ��ɾ��һ����¼
     * $mongo->remove("ttt", array("title"=>"bbb"), array("justOne"=>1));
     * ��ȡMongo�����Ĵ�����Ϣ
     * $mongo->getError();
     */

}