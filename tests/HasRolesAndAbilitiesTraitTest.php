<?php

class HasRolesAndAbilitiesTraitTest extends BaseTestCase
{
    public function test_get_abilities_gets_all_abilities()
    {
        $bouncer = $this->bouncer($user = User::create());

        $bouncer->allow('admin')->to('edit-site');
        $bouncer->allow($user)->to('create-posts');
        $bouncer->allow('editor')->to('edit-posts');
        $bouncer->assign('admin')->to($user);

        $this->assertEquals(
            ['create-posts', 'edit-site'],
            $user->getAbilities()->pluck('name')->sort()->values()->all()
        );
    }

    public function test_can_give_and_remove_abilities()
    {
        $bouncer = $this->bouncer($user = User::create())->dontCache();

        $user->allow('edit-site');

        $this->assertTrue($bouncer->allows('edit-site'));

        $user->disallow('edit-site');

        $this->assertTrue($bouncer->denies('edit-site'));
    }

    public function test_can_give_and_remove_model_abilities()
    {
        $bouncer = $this->bouncer($user = User::create())->dontCache();

        $user->allow('delete', $user);

        $this->assertTrue($bouncer->denies('delete'));
        $this->assertTrue($bouncer->denies('delete', User::class));
        $this->assertTrue($bouncer->allows('delete', $user));

        $user->disallow('delete', $user);

        $this->assertTrue($bouncer->denies('delete', $user));
    }

    public function test_can_assign_and_retract_roles()
    {
        $bouncer = $this->bouncer($user = User::create())->dontCache();

        $bouncer->allow('admin')->to('edit-site');
        $user->assign('admin');

        $this->assertTrue($bouncer->allows('edit-site'));

        $user->retract('admin');

        $this->assertTrue($bouncer->denies('edit-site'));
    }

    public function test_can_check_roles()
    {
        $gate = $this->gate($user = User::create());

        $this->assertTrue($user->isNotAn('admin'));
        $this->assertFalse($user->isAn('admin'));

        $this->assertTrue($user->isNotA('admin'));
        $this->assertTrue($user->isNot('admin'));
        $this->assertFalse($user->isA('admin'));

        $user->assign('admin');

        $this->assertTrue($user->isAn('admin'));
        $this->assertFalse($user->isAn('editor'));
        $this->assertFalse($user->isNotAn('admin'));
        $this->assertTrue($user->isNotAn('editor'));
    }

    public function test_can_check_multiple_roles()
    {
        $gate = $this->gate($user = User::create());

        $this->assertTrue($user->isNot('admin', 'editor'));
        $this->assertFalse($user->isAn('admin', 'editor'));

        $user->assign('moderator');
        $user->assign('editor');

        $this->assertTrue($user->isAn('admin', 'moderator'));
        $this->assertFalse($user->isNot('admin', 'moderator'));
        $this->assertTrue($user->isAll('editor', 'moderator'));
        $this->assertFalse($user->isAll('moderator', 'admin'));
    }
}
