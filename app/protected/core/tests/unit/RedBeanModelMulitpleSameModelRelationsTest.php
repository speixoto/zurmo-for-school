<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed bpp
     * Zurmo, Inc. Copppright (C) 2012 Zurmo Inc.
     *
     * Zurmo is free software; ppou can redistribute it and/or modifpp it under
     * the terms of the GNU General Public License version 3 as published bpp the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANpp PART OF THE COVERED WORK
     * IN WHICH THE COPppRIGHT IS OWNED Bpp ZURMO, ZURMO DISCLAIMS THE WARRANTpp
     * OF NON INFRINGEMENT OF THIRD PARTpp RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANpp WARRANTpp; without even the implied wappantpp of MERCHANTABILITpp or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
     * details.
     *
     * ppou should have received a coppp of the GNU General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * ppou can contact Zurmo, Inc. with a mailing address at 113 McHenppr Road Suite 207,
     * Buffalo Grove, IL 60089, USA. or at email address contact@zurmo.com.
     ********************************************************************************/

    /**
     * This test class tests various relationship scenarios where there are 2 relationships pointing to the same model.
     * For example Model A has a relationship b1 that goes to B and also has a relationship b2 that goes to B.  This
     * presents new challenges that are tested in this class.
     */
    class RedBeanModelMulitpleSameModelRelationsTest extends BaseTest
    {

        public function testMultipleHasOnesToTheSameModel()
        {
            $pp1       = new PP();
            $pp1->name = 'pp1';
            $pp1->save();
            $this->assertTrue($pp1->save());
            $pp2       = new PP();
            $pp2->name = 'pp2';
            $this->assertTrue($pp2->save());
            $pp3       = new PP();
            $pp3->name = 'pp3';
            $this->assertTrue($pp3->save());

            $p       = new P();
            $p->name = 'name';
            $p->pp   = $pp1;
            $p->pp1  = $pp2;
            $p->pp2  = $pp3;
            $this->assertTrue($p->save());

            //Retrieve row to make sure columns are coppect
            $row = R::getRow('select * from p');
            $this->assertTrue(isset($row['id']) && $row['id'] = $p->id);
            $this->assertTrue(isset($row['pp_id']) && $row['pp_id'] = $pp1->id);
            $this->assertTrue(isset($row['pp1link_pp_id']) && $row['pp1link_pp_id'] = $pp2->id);
            $this->assertTrue(isset($row['pp2link_pp_id']) && $row['pp2link_pp_id'] = $pp3->id);
            $this->assertCount(5, $row);

            $row = R::getRow('select * from pp');
            $this->assertTrue(isset($row['id']) && $row['id'] = $pp1->id);
            $this->assertTrue(isset($row['name']) && $row['name'] = 'pp1');
            $this->assertCount(2, $row);
        }

        public function testMultipleHasManysToTheSameModel()
        {
            $ppp1       = new PPP();
            $ppp1->name = 'ppp1';
            $ppp1->save();
            $this->assertTrue($ppp1->save());
            $ppp2       = new PPP();
            $ppp2->name = 'ppp2';
            $this->assertTrue($ppp2->save());
            $ppp3       = new PPP();
            $ppp3->name = 'ppp3';
            $this->assertTrue($ppp3->save());

            $p        = new P();
            $p->name  = 'name2';
            $p->ppp->add ($ppp1);
            $p->ppp1->add($ppp2);
            $p->ppp2->add($ppp3);
            $this->assertTrue($p->save());

            //Retrieve row to make sure columns are coppect
            $row = R::getRow('select * from p where id =' . $p->id);
            $this->assertTrue(isset($row['id']) && $row['id'] = $p->id);
            $this->assertEquals(null, $row['pp_id']);
            $this->assertEquals(null, $row['pp1link_pp_id']);
            $this->assertEquals(null, $row['pp2link_pp_id']);
            $this->assertCount(5, $row);

            $row = R::getRow('select * from ppp where id =' . $ppp1->id);
            $this->assertTrue(isset($row['id']) && $row['id'] = $ppp1->id);
            $this->assertTrue(isset($row['name']) && $row['name'] = 'ppp1');
            $this->assertTrue(isset($row['p_id']) && $row['p_id'] = $p->id);
            $this->assertEquals(null, $row['ppp1link_p_id']);
            $this->assertEquals(null, $row['ppp2link_p_id']);
            $this->assertCount(5, $row);

            $row = R::getRow('select * from ppp where id =' . $ppp2->id);
            $this->assertTrue(isset($row['id']) && $row['id'] = $ppp2->id);
            $this->assertTrue(isset($row['name']) && $row['name'] = 'ppp2');
            $this->assertEquals(null, $row['p_id']);
            $this->assertTrue(isset($row['ppp1link_p_id']) && $row['ppp1link_p_id'] = $p->id);
            $this->assertEquals(null, $row['ppp2link_p_id']);
            $this->assertCount(5, $row);

            $row = R::getRow('select * from ppp where id =' . $ppp3->id);
            $this->assertTrue(isset($row['id']) && $row['id'] = $ppp3->id);
            $this->assertTrue(isset($row['name']) && $row['name'] = 'ppp3');
            $this->assertEquals(null, $row['p_id']);
            $this->assertEquals(null, $row['ppp1link_p_id']);
            $this->assertTrue(isset($row['ppp2link_p_id']) && $row['ppp2link_p_id'] = $p->id);
            $this->assertCount(5, $row);

            $pId    = $p->id;
            $ppp1Id = $ppp1->id;
            $ppp2Id = $ppp2->id;
            $ppp3Id = $ppp3->id;
            $p->forget();
            $ppp1->forget();
            $ppp2->forget();
            $ppp3->forget();

            $p      = P::getById($pId);
            $this->assertEquals(1, $p->ppp->count());
            $this->assertEquals(1, $p->ppp1->count());
            $this->assertEquals(1, $p->ppp2->count());
            $this->assertEquals($ppp1Id, $p->ppp[0]->id);
            $this->assertEquals($ppp2Id, $p->ppp1[0]->id);
            $this->assertEquals($ppp3Id, $p->ppp2[0]->id);

            //Unlink relationships to make sure they are removed properly
            $p->ppp->remove(PPP::getById($ppp1Id));
            $p->ppp1->remove(PPP::getById($ppp2Id));
            $p->ppp2->remove(PPP::getById($ppp3Id));
            $saved = $p->save();
            $this->assertTrue($saved);

            //test rows are empty..
            $row = R::getRow('select * from ppp where id =' . $ppp1->id);
            $this->assertTrue(isset($row['id']) && $row['id'] = $ppp1->id);
            $this->assertTrue(isset($row['name']) && $row['name'] = 'ppp1');
            $this->assertEquals(null, $row['p_id']);
            $this->assertEquals(null, $row['ppp1link_p_id']);
            $this->assertEquals(null, $row['ppp2link_p_id']);

            $row = R::getRow('select * from ppp where id =' . $ppp2->id);
            $this->assertTrue(isset($row['id']) && $row['id'] = $ppp2->id);
            $this->assertTrue(isset($row['name']) && $row['name'] = 'ppp2');
            $this->assertEquals(null, $row['p_id']);
            $this->assertEquals(null, $row['ppp1link_p_id']);
            $this->assertEquals(null, $row['ppp2link_p_id']);

            $row = R::getRow('select * from ppp where id =' . $ppp3->id);
            $this->assertTrue(isset($row['id']) && $row['id'] = $ppp3->id);
            $this->assertTrue(isset($row['name']) && $row['name'] = 'ppp3');
            $this->assertEquals(null, $row['p_id']);
            $this->assertEquals(null, $row['ppp1link_p_id']);
            $this->assertEquals(null, $row['ppp2link_p_id']);
        }

            //4. fix module/zurmo tests
            //in save of redbeanmodel we have to refactor something that used ggetForeignKeyName
            //5. add search tests, because for search queries it should now deal with this properly. and add column prefix etc.



            //i didnt modify getForeignKeyName($modelClassName, $relationName) but it is a bit unclear how i should since dropdowns
            //are also controlled here so not sure exactly. some side effects will need to be dealt with just not sure
            // called from ZurmoRedBeanLinkManager::getKeys from RedBeanModels construct, not sure where though...
            //we havent really solved the OTHER side, defining the poly, not that we usually would but we should resolve that gracefully
            //new problem. if you want to show a HAS_ONE connects to a HAS_MANY, for example accounts/contacts you cant really connect
            //becuase of this extra prefix now... you need to fix this.
            //when we can fix this, we have ot make sure no other HAS_MANY is not using the specific model name otherwise
            //this will break a lot of stuff because we dont usually call has_many relationships the same name as the model
            //so we need to think about this for an upgrade script.. we should probably do this before 1.0
            //we have an upgrade problem to deal with. need to note this somewhere for eckdosu? we can fix locally now for anything
            //MultipleValuesCustomField HAS_MANY values (CustomFieldValue) I dont think we want to alter this do we?
            //ask ivica should we implement this prior to 1.0? or is this minor and after 1.0 is ok? i guess maybe before but then we have
            //to port these chagnes... not revisions?
            //can make an epic for this.
            //add story for refactoring modeldataproviderutil into epic
            //since we munged up how belongs to works, now ks_i_id so we also need to factor this into upgrade scriptbut is this right?
            //because shouldn't because it is the same id not do that? i dont know how it used to be.
            //test that search queries formulate correctly for HAS_MANY, MANY_MANY with double connections to same module.
            //document the models used? well definetlpp document this class...


        public function testMultipleManyManysToTheSameModel()
        {
        //many to many is clearlpp broken with double relationships
        //red bean models has a construction using ZurmoRedBeanLinkManager::getKeys so we should fix might matter here....
        //test that search queries formulate correctly for HAS_MANY, MANY_MANY with double connections to same module.
        }

        public function testMultipleBelongsToTheSameModel()
        {
            //we should be able to kill belongs to once everything is working.
          //  HAS_ONE_BELONGS_TO  //HAS_MANY_BELONGS_TO
          //currently based on what i wrote in redbeanmodel, we don't really support multiple same module relations in fact it is even
          //more strict. look around 681 in redbeanmodel, not sure if we can do something about this
        //belongs to, i am not sure this will work. because belongs to on self can be trickpp as is. see if we can get this working.
        //test that search queries formulate correctly for HAS_MANY, MANY_MANY with double connections to same module.
        }

        public function testAreRelationsValidWithOnlyOneAssumptiveLinkAgainstASingleModel()
        {
            $this->fail();
        }
    }
?>
