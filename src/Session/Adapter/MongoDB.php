<?php
namespace Kof\Phalcon\Session\Adapter;

use Phalcon\Session\AdapterInterface;
use Phalcon\Session\Exception;

class MongoDB extends IniSet implements AdapterInterface
{
    /**
     * @var \MongoDB\Driver\Manager
     */
    protected $mongoDBDriverManager = null;

    /**
     * @var string
     */
    protected $mongoDBNamespace = null;

    /**
     * @var \MongoDB\Driver\WriteConcern
     */
    protected $mongoDBWriteConcern;

    /**
     * Class constructor.
     *
     * @param  array     $options
     * @throws Exception
     */
    public function __construct($options = null)
    {
        if (!isset($options['mongoDBDriverManager']) ||
            !$options['mongoDBDriverManager'] instanceof \MongoDB\Driver\Manager
        ) {
            throw new Exception(
                'Parameter "mongoDBDriverManager" is required and it must be an instance of MongoDB\Driver\Manager'
            );
        }

        if (!isset($options['mongoDBNamespace']) ||
            empty($options['mongoDBNamespace']) ||
            !is_string($options['mongoDBNamespace'])
        ) {
            throw new Exception("Parameter 'mongoDBNamespace' is required and it must be a non empty string");
        }

        $this->mongoDBDriverManager = $options['mongoDBDriverManager'];
        $this->mongoDBNamespace = $options['mongoDBNamespace'];
        $this->mongoDBWriteConcern = new \MongoDB\Driver\WriteConcern(\MongoDB\Driver\WriteConcern::MAJORITY, 1000);

        unset($options['mongoDBDriverManager'], $options['mongoDBNamespace']);

        session_set_save_handler(
            [$this, 'open'],
            [$this, 'close'],
            [$this, 'read'],
            [$this, 'write'],
            [$this, 'destroy'],
            [$this, 'gc']
        );

        parent::__construct($options);
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean
     */
    public function open()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
    }

    /**
     * {@inheritdoc}
     *
     * @param  string $sessionId
     * @return string
     */
    public function read($sessionId)
    {
        try {
            $query = new \MongoDB\Driver\Query([
                '_id' => $sessionId
            ], [
                'limit' => 1
            ]);
            $cursor = $this->mongoDBDriverManager->executeQuery(
                $this->mongoDBNamespace,
                $query
            );

            $cursorArray = $cursor->toArray();
            if (!$cursorArray) {
                return '';
            }
            $cursorArray = current($cursorArray);
        } catch (\Exception $e) {
            return '';
        }

        return $cursorArray->data;
    }

    /**
     * {@inheritdoc}
     *
     * @param  string $sessionId
     * @param  string $sessionData
     * @return bool
     */
    public function write($sessionId, $sessionData)
    {
        try {
            $bulkWrite = new \MongoDB\Driver\BulkWrite;
            $bulkWrite->update([
                '_id' => $sessionId
            ], [
                '_id' => $sessionId,
                'modified' => time(),
                'data' => $sessionData
            ], [
                'multi' => false,
                'upsert' => true
            ]);
            $this->mongoDBDriverManager->executeBulkWrite(
                $this->mongoDBNamespace,
                $bulkWrite,
                $this->mongoDBWriteConcern
            );
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($sessionId = null)
    {
        if (is_null($sessionId)) {
            $sessionId =$this->getId();
        }

        try {
            $bulkWrite = new \MongoDB\Driver\BulkWrite;
            $bulkWrite->delete([
                '_id' => $sessionId
            ]);
            $this->mongoDBDriverManager->executeBulkWrite(
                $this->mongoDBNamespace,
                $bulkWrite,
                $this->mongoDBWriteConcern
            );
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     * @param string $maxLifetime
     */
    public function gc($maxLifetime)
    {
        try {
            $minAge = new \DateTime();
            $minAge->sub(new \DateInterval('PT' . $maxLifetime . 'S'));
            $bulkWrite = new \MongoDB\Driver\BulkWrite;
            $bulkWrite->delete([
                'modified' => ['$lte' => $minAge->getTimestamp()]
            ]);
            $this->mongoDBDriverManager->executeBulkWrite(
                $this->mongoDBNamespace,
                $bulkWrite,
                $this->mongoDBWriteConcern
            );
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}
