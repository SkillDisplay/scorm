<?php

declare(strict_types = 1);

namespace Skilldisplay\Scorm;

use SkillDisplay\PHPToolKit\Entity\SkillSet;

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

    public function writeSkillToHTML(\SkillDisplay\PHPToolKit\Entity\Skill $skill, array $relatedSkillSets){

        $template = $this->twig->load('Skill.html');
        $shorttitle = $this->getShortTitleFromTitle($skill->getTitle());
        file_put_contents(

            $this->OutputDirectory.DIRECTORY_SEPARATOR.'skills'.DIRECTORY_SEPARATOR.$shorttitle.'.html',
            $template->render([
                'skill' => $skill->toArray(),
                'relatedLearner' => ((array_key_exists('Learner', $relatedSkillSets) ? $relatedSkillSets['Learner'] : array())),
                'relatedEducation' => ((array_key_exists('Education', $relatedSkillSets) ? $relatedSkillSets['Education'] : array())),
                'relatedBusiness' => ((array_key_exists('Business', $relatedSkillSets) ? $relatedSkillSets['Business'] : array())),
                'relatedCertifier' => ((array_key_exists('Certifier', $relatedSkillSets) ? $relatedSkillSets['Certifier'] : array()))
            ])
        );
    }

    public function writeImsManifest(SkillSet $skillset, array $skills){
        $template = $this->twig->load('imsmanifest_template.xml');

        $skillsarray = array();
        foreach($skills as $skill){
            /* @var \SkillDisplay\PHPToolKit\Entity\Skill $skill */
            $skillar = $skill->toArray();
            $skillar['shorttitle'] = $this->getShortTitleFromTitle($skillar['title']);
            $skillsarray[] = $skillar;
        }

        file_put_contents(
            $this->OutputDirectory.DIRECTORY_SEPARATOR.'imsmanifest.xml',
            $template->render([
                'skillset' => $skillset,
                'skills' => $skillsarray
            ])
        );
    }

    public function getShortTitleFromTitle(string $title) : string {
        return strtolower(trim(str_replace(' ', '_', str_replace('|','',$title))));
    }

}