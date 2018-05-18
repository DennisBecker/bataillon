<?php
/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class RoboFile extends \Robo\Tasks
{
    public function watch() {
        $this->taskExec('php bataillon.php build')->run();

        $this->taskWatch()
            ->monitor('composer.json', function() {
                $this->taskComposerUpdate()->run();
            })->monitor('src', function() {
                $this->taskExec('php bataillon.php build')->run();
            })->run();
    }
}