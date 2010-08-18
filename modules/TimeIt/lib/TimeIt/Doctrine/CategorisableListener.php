<?php

/**
 * This listener manages dynamic categories of a record.
 */
class TimeIt_Doctrine_CategorisableListener extends Doctrine_Record_Listener
{
    public function preHydrate(Doctrine_Event $event)
    {

        $i = 1;
    }

    public function postHydrate(Doctrine_Event $event)
    {
        $i = 1;
    }



//    /**
//     * (non-PHPdoc)
//     * @see includes/externals/Doctrine/Doctrine/Record/Doctrine_Record_Listener#postHydrate()
//     */
////    public function postHydrate(Doctrine_Event $event)
////    {
////        if (System::getVar('Z_CONFIG_USE_OBJECT_CATEGORIZATION') === false
////            || !ModUtil::available('Categories')) {
////            return;
////        }
////
////        if(is_object($event->data)) {
////            $event->data->mapValue('__CATEGORIES__', array());
////        }
////
////        $data = $event->data;
////        $tableName = $this->getTableNameFromEvent($event);
////        $idColumn = $this->getIdColumnFromEvent($event);
////
////        $event->data = ObjectUtil::expandObjectWithCategories($data, $tableName, $idColumn);
////    }
//
//
//    /**
//     * (non-PHPdoc)
//     * @see includes/externals/Doctrine/Doctrine/Record/Doctrine_Record_Listener#postInsert()
//     */
////    public function postInsert(Doctrine_Event $event)
////    {
////        if (System::getVar('Z_CONFIG_USE_OBJECT_CATEGORIZATION') === false
////            || !ModUtil::available('Categories')) {
////            return;
////        }
////
////        $data = $event->getInvoker()->toArray();
////        $tableName = $this->getTableNameFromEvent($event);
////        $idColumn = $this->getIdColumnFromEvent($event);
////
////        ObjectUtil::storeObjectCategories($data, $tableName, $idColumn);
////    }
//
//    /**
//     * (non-PHPdoc)
//     * @see includes/externals/Doctrine/Doctrine/Record/Doctrine_Record_Listener#postUpdate()
//     */
//    public function postUpdate(Doctrine_Event $event)
//    {
//        if (System::getVar('Z_CONFIG_USE_OBJECT_CATEGORIZATION') === false
//            || !ModUtil::available('Categories')) {
//            return;
//        }
//
//        $data = $event->getInvoker();
//        $tableName = $this->getTableNameFromEvent($event);
//        $idColumn = $this->getIdColumnFromEvent($event);
//
//        ObjectUtil::storeObjectCategories($data, $tableName, $idColumn, true);
//    }
//
//
//
//    /**
//     * (non-PHPdoc)
//     * @see includes/externals/Doctrine/Doctrine/Record/Doctrine_Record_Listener#postDelete()
//     */
//    public function postDelete(Doctrine_Event $event)
//    {
//        if (System::getVar('Z_CONFIG_USE_OBJECT_CATEGORIZATION') === false
//            || !ModUtil::available('Categories')) {
//            return;
//        }
//
//        $data = $event->getInvoker();
//        $tableName = $this->getTableNameFromEvent($event);
//        $idColumn = $this->getIdColumnFromEvent($event);
//
//        ObjectUtil::deleteObjectCategories($data, $tableName, $idColumn);
//    }
//
//    private function getTableFromEvent(Doctrine_Event $event)
//    {
//        $treatedRecord = $event->getInvoker();
//        if($treatedRecord instanceof Doctrine_Record) {
//            $recordClass = get_class($treatedRecord);
//            return Doctrine::getTable($recordClass);
//        } else if($treatedRecord instanceof Doctrine_Table) {
//            return $treatedRecord;
//        } else {
//            throw new LogicException("TimeIt_Doctrine_CategorisableListener::getTableFromEvent unknown invoker: "+  get_class($treatedRecord));
//        }
//    }
//
//    private function getTableNameFromEvent(Doctrine_Event $event)
//    {
//        $tableRef = $this->getTableFromEvent($event);
//        sscanf($tableRef->getTableName(), Doctrine_Manager::getInstance()->getAttribute(Doctrine::ATTR_TBLNAME_FORMAT), $tableName);
//
//        return $tableName;
//    }
//
//    private function getIdColumnFromEvent(Doctrine_Event $event)
//    {
//        $tableRef = $this->getTableFromEvent($event);
//
//        $idColumn = $tableRef->getIdentifier();
//
//        if(is_array($idColumn)) {
//            // TODO support multiple columns as primary key?
//            $idColumn = $idColumn[0];
//        }
//
//        return $idColumn;
//    }
}
