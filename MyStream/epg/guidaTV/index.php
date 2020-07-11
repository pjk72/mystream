<?php

$doc = new DOMDocument();
$xsl = new XSLTProcessor();

$doc->load(__DIR__ . '/leprogramme.xslt');
$xsl->importStyleSheet($doc);

$doc->load(__DIR__ . '/a.xml');
$data = $xsl->transformToXML($doc);
//file_put_contents(__DIR__.'/result.html', $data);
libxml_use_internal_errors(true);
$doc = new DOMDocument();
$doc->loadHTML($data);
$xpath = new DOMXPath($doc);

$chaines = array();
$domChannels = $xpath->query('/html/body/div');
foreach ($domChannels as $domChannel) {
    $channel = new Channel();
    $id = $domChannel->getAttribute('id');
    $channel->id = substr($id, strpos($id, '_')+1);
    $channel->setIcon($channel->id);
    $domChaine = $xpath->query('div[@class="chaine"]', $domChannel)->item(0);
    $channel->name = $domChaine->getAttribute('title');
    $domPrograms = $xpath->query('div[@class="programme"]/div[starts-with(@class, "emission")]/div/div[starts-with(@id, "data_")]', $domChaine);
    foreach ($domPrograms as $domEmission) {
        $program = new Program();
        $object = json_decode($domEmission->nodeValue);
        $program->fromObject($object);
        $channel->addProgram($program);
    }
    $chaines[] = $channel;
}
$view = new View();
$view->channels = $chaines;
$view->startHour = 15;
$view->endHour = 19;
$view->beginDate = mktime(15, 0, 0, 11, 12, 2012);
$view->endDate = mktime(19, 0, 0, 11, 12, 2012);
echo $view->run(dirname(__FILE__).'/templates/grille.php');

class View
{

    /**
     * Processes a view script and returns the output.
     *
     * @param string $name The script name to process.
     * @return string The script output.
     */
    public function render($name)
    {
        // find the script file name using the parent private method
        $this->_file = $this->_script($name);
        unset($name); // remove $name from local scope

        ob_start();
        $this->_run($this->_file);

        return $this->_filter(ob_get_clean()); // filter output
    }

    public function run($file)
    {
        ob_start();
        include $file;
        return ob_get_clean(); // filter output
    }
}

class Channel
{
    /**
     * @var integer
     */
    public $id;
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $icon;
    /**
     * @var Program[]
     */
    public $programs = array();

    public function addProgram($program)
    {
        $this->programs[] = $program;
        return $this;
    }

    public function setIcon($icon)
    {
        $this->icon = 'http://icon-telerama.sdv.fr/tele/imedia/images_chaines_tra/Transparent/40x40/'
            . $icon . '.gif';
        return $this;
    }
}

class Program
{
    /**
     * @var Chaine
     */
    public $channel;
    /**
     * @var string
     */
    public $id;
    /**
     * @var string
     */
    public $title;
    /**
     * @var string
     */
    public $subTitle;
    /**
     * @var string
     */
    public $url;
    /**
     * @var string
     */
    public $showView;
    /**
     * Timestamp
     *
     * @var integer
     */
    public $start;
    /**
     * Timestamp
     *
     * @var integer
     */
    public $stop;
    /**
     * @var string
     */
    public $date;
    /**
     * @var string
     */
    public $desc;
    /**
     * @var Category
     */
    public $category;
    /**
     * @var string
     */
    public $language;
    /**
     * @var string
     */
    public $origLanguage;
    /**
     * @var Length
     */
    public $length;
    /**
     * @var string
     */
    public $country;
    /**
     * @var Episode
     */
    public $episode;
    /**
     * @var Video
     */
    public $video;
    /**
     * @var Audio
     */
    public $audio;
    /**
     * @var string
     */
    public $subtitles;
    /**
     * @var string
     */
    public $rating;
    /**
     * @var string
     */
    public $starRating;
    /**
     * @var Credits
     */
    public $credits;

    public function fromObject($object)
    {
        $this->id = $object->Id_Emission;
        $this->start = $this->buildDatetime($object->Date_Debut);
        $this->stop = $this->buildDatetime($object->Date_Fin);
        $this->title = trim($object->Titre);
        $this->subTitle = trim($object->Sous_Titre);
        $this->url = 'http://television.telerama.fr/tele/programmes-tv/'
            . $object->Titre_Url . ',' . $this->id . '.php';
        $this->showView = $object->ShowView;
        $this->setStarRating($object->note_T);
        $this->setCategory($object->Type);
        $this->setLength($object->DureeEnSecondes, 'seconds');
        $this->setEpisode($object->episode_numero, $object->saison_numero);
        $this->desc = trim($object->resume_long);
        $this->setCredits($object->intervenant);
    }

    public function buildDatetime($date)
    {
        // 2012-12-11 16:35:00
        $tmp = explode(' ', trim($date));
        $date = $tmp[0];
        $time = $tmp[1];
        $tmpDate = explode('-', $date);
        $tmpTime = explode(':', $time);
        return mktime($tmpTime[0], $tmpTime[1], 0, $tmpDate[2], $tmpDate[1], $tmpDate[0]);
    }

    public function setStarRating($note)
    {
        if (empty($note)) {
            return;
        }
        $note = (int)$note;
        if ($note == 1) {
            $this->starRating = '2/5';
        } elseif ($note == 2) {
            $this->starRating = '3/5';
        } elseif ($note == 3) {
            $this->starRating = '4/5';
        } elseif ($note == 5) {
            $this->starRating = '1/5';
        } elseif ($note > 5) {
            $this->starRating = '0/5';
        }
    }

    public function setCategory($type)
    {
        $this->category = new Category();
        $this->category->name = $type;
        $this->category->lang = 'fr';
    }

    public function setLength($length, $unit = 'seconds')
    {
        $this->length = new Length();
        if ($unit == 'minutes') {
            $length = $length * 60;
        } elseif ($unit == 'hours') {
            $length = $length * 60 * 60;
        }
        $unit = 'seconds';
        $this->length->number = $length;
        $this->length->unit = $unit;
    }

    public function setEpisode($episodeNum, $saisonNum)
    {
        if (empty($episodeNum)) {
            return;
        }
        $this->episode = new Episode();
        $this->episode->number = $episodeNum;
        $this->episode->saison = $saisonNum;
    }

    public function setCredits($credits)
    {
        if (!empty($credits)) {
            $this->credits = urldecode($credits);
        }
    }
}

class Episode
{
    /**
     * @var integer
     */
    public $number;
    /**
     * @var string
     */
    public $saison;
}

class Credits
{
    /**
     * @var Person[]
     */
    public $directors;
    /**
     * @var Person[]
     */
    public $actors;
    /**
     * @var Person[]
     */
    public $writers;
    /**
     * @var Person[]
     */
    public $producers;
    /**
     * @var Person[]
     */
    public $presenters;
    /**
     * @var Person[]
     */
    public $commentators;
    /**
     * @var Person[]
     */
    public $guests;
}

class Category
{
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $lang;
}

class Length
{
    /**
     * @integer
     */
    public $number;
    /**
     * (seconds | minutes | hours)
     *
     * @var string
     */
    public $unit;
}

class Person
{
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $role;
}

class Video
{
    /**
     * @var boolean
     */
    public $present;
    /**
     * @var boolean
     */
    public $colour;
    /**
     * 4/3 or 16/9
     *
     * @var string
     */
    public $aspect;
    /**
     * @var string
     */
    public $quality;
}

class Audio
{
    /**
     * @var boolean
     */
    public $present;

    /**
     * 'mono','stereo','dolby','dolby digital','bilingual' and 'surround'
     *
     * @var string
     */
    public $stereo;
}
?>
