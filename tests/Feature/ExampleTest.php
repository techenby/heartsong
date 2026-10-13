<?php

test('the app opens on the classes page', function () {
    $this->get(route('home'))->assertRedirect(route('courses.index'));
});
