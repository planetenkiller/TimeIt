<?php

/**
 * This template manages all record concerns related to it's dynamic categories.
 *
 * @see CategorisableListener
 */
class TimeIt_Doctrine_Categorisable extends Doctrine_Template
{
//    public function setUp()
//    {
//        // Turn on object categorisation.
//        // This listener gets a name to be able to disable and reenable it dynamically during runtime.
////        $this->addListener(new TimeIt_Doctrine_CategorisableListener(), 'CategoryListener');
////        $this->_table->unshiftFilter(new TimeIt_Doctrine_CategorisableFilter());
//    }

    public function setCategory($prop, TimeIt_Model_Cat $category) {
        $rec = $this->getInvoker();

        $mapobjFound = null;
        foreach ($rec['Categories'] as $mapobj) {
            if($mapobj['regid'] == $prop) {
                $mapobjFound = $mapobj;
                break;
            }
        }

        if($mapobjFound != null) {
            $mapobjFound['regid'] = $prop;
        } else {
            $rec['Categories'][]['regid'] = $prop;
            $newmapobj = $rec['Categories']->getLast();
            $newmapobj['catid'] = $category->get('catid');
        }
    }
}

