<?php


namespace Skilldisplay\Scorm;

class Writer
{
    private \Twig\Environment $twig;
    private string $OutputDirectory;

    public function __construct(string $TwigTemplateDirectory, string $OutputDirectory){
        $this->OutputDirectory = $OutputDirectory;
        // initialize Templating Engine
        $loader = new \Twig\Loader\FilesystemLoader($TwigTemplateDirectory);
        $this->twig = new \Twig\Environment($loader);
    }

    public function writeSkillToHTML(\SkillDisplay\PHPToolKit\Entity\Skill $skill){

        $template = $this->twig->load('Skill.html');
        file_put_contents(
            $this->OutputDirectory.DIRECTORY_SEPARATOR.trim(str_replace(' ', '_', $skill->getTitle())).'.html',
            $template->render(['skill' => $skill->toArray()])
        );

    }
}