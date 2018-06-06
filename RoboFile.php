<?php
/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class RoboFile extends \Robo\Tasks
{
    public function watch() {
        $this->build();

        $this->taskServer(8000)
            ->dir('dist')
            ->background()
            ->run();

        $this->taskOpenBrowser('http://localhost:8000')
            ->run();

        $this->taskWatch()
            ->monitor('composer.json', function() {
                $this->taskComposerUpdate()->run();
            })->monitor('src', function() {
                $this->build();
            })->run();
    }

    public function build() {
        $this->_cleanDir('./dist');

        $this->taskExec('php bataillon.php build')->run();
    }

    public function update() {
        $this->taskExec('php bataillon.php update')->run();
    }
}