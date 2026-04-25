<?php

test('root redirects to notes index', function () {
    $this->get('/')->assertRedirect(route('commission-notes.index'));
});
