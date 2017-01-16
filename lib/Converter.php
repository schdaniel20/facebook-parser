<?php

namespace Cylex\Facebook\Parser;

class Converter {

    protected $allowed = [
        'head office' => 'Head Office',
        'sales' => 'Sales',
        'general enquiries' => 'General Enquiries',
        'reception' => 'Reception',
        'helpline' => 'Helpline',
        'newsdesk' => 'Newsdesk',
        'box office' => 'Box Office',
        'customer services' => 'Customer Services',
        'central office' => 'Central Office',
        'main office' => 'Main Office',
        'booking' => 'Bookings',
        'fax' => 'Fax'
    ];

    protected function cleanDepText($text) {
        if (preg_match("~((?:[\-\+\:\(\)\[\]]*(?:\s*(?:and|or))?)(?:\s*[\-\+\:\(\)\[\]]*)?)~ims", $text, $match)) {
            if ($match[1] == $text) {
                return "";
            }
        }

        foreach ($this->allowed as $key => $value) {
            if (stristr($text, $key)) {
                return $value;
            }
        }
        
        return "";
    }

    public function createPhoneJson($phoneText) {
        $originalPhone = $phoneText;
        $jsonPhone = "[";

        //ha betuvel kezdodik
        if (preg_match('~^[a-zA-Z]~ims', $phoneText)) {
            if (preg_match_all('~([a-zA-Z\s&\(\)\']{2,})\s*[\:\-\[\]\(\)\.;]\s*([0-9\-\(\)\s\+\/\[\]\.]{6,})~ims', $phoneText, $matches)) {

                for ($i = 0; $i < count($matches[1]); $i++) {
                    $jsonPhone .= '{"Phone": "' . $matches[2][$i] . '", "Info":"' . $this->cleanDepText(trim($matches[1][$i])) . '"},';
                    $phoneText = str_replace(array($matches[1][$i], $matches[2][$i]), '', $phoneText);
                }

                if (preg_match_all('~([0-9\-\(\)\s\+\/\[\]\.]{4,})~ims', $phoneText, $remains)) {
                    foreach ($remains[1] as $m) {
                        $jsonPhone .= '{"Phone": "' . $m . '", "Info":""},';
                    }
                }
            } elseif (preg_match_all('~([a-zA-Z\s&\(\)\'\s]{2,})\s*([0-9\-\(\)\s\+\/\[\]\.]{6,})~ims', $phoneText, $matches)) {

                for ($i = 0; $i < count($matches[1]); $i++) {
                    $jsonPhone .= '{"Phone": "' . $matches[2][$i] . '", "Info":"' . $this->cleanDepText(trim($matches[1][$i])) . '"},';
                    $phoneText = str_replace(array($matches[1][$i], $matches[2][$i]), '', $phoneText);
                }
                if (preg_match_all('~([0-9\-\(\)\s\+\/\[\]\.]{4,})~ims', $phoneText, $remains)) {
                    foreach ($remains[1] as $m) {
                        $jsonPhone .= '{"Phone": "' . $m . '", "Info":""},';
                    }
                }
            }
        } else { //ha az elejen 1 telefonszam van
            // van jo elvalaszto kozottuk [\:\-\[\]\(\);]
            if (preg_match_all('~([0-9\-\(\)\s\+\/\[\]\.]{6,})\s*[\:\-\[\]\(\);]\s*([a-zA-Z\s&\(\)\']{2,})~ims', $phoneText, $matches)) {
                for ($i = 0; $i < count($matches[1]); $i++) {
                    $jsonPhone .= '{"Phone": "' . $matches[1][$i] . '", "Info":"' . $this->cleanDepText(trim($matches[2][$i])) . '"},';
                    $phoneText = str_replace(array($matches[1][$i], $matches[2][$i]), '', $phoneText);
                }

                if (preg_match_all('~([0-9\-\(\)\s\+\/\[\]\.]{4,})~ims', $phoneText, $remains)) {
                    foreach ($remains[1] as $m) {
                        $jsonPhone .= '{"Phone": "' . $m . '", "Info":""},';
                    }
                }
            }// nincs elvalaszto jel es a text utan nincs ":" vagy - (ha van akkor az masik eset)
            elseif (preg_match_all('~((?:[\d\-\(\)\s\+\/\[\]\.]{6,}\d)\W+)\b((?:(?!\:|\-|[0-9]).)+?)\b\b\d~ims', $phoneText, $matches)) {
                for ($i = 0; $i < count($matches[1]); $i++) {
                    $jsonPhone .= '{"Phone": "' . $matches[1][$i] . '", "Info":"' . $this->cleanDepText(trim($matches[2][$i])) . '"},';
                    $phoneText = str_replace(array($matches[1][$i], $matches[2][$i]), '', $phoneText);
                }

                if (preg_match_all('~([0-9\-\(\)\s\+\/\[\]\.]{4,})~ims', $phoneText, $remains)) {
                    foreach ($remains[1] as $m) {
                        $jsonPhone .= '{"Phone": "' . $m . '", "Info":""},';
                    }
                }
            } elseif (preg_match_all('~([a-zA-Z\s&\(\)\']{2,})\s*[\:\-\[\]\(\)\.;]\s*([0-9\-\(\)\s\+\/\[\]\.]{6,})~ims', $phoneText, $matches)) {
                for ($i = 0; $i < count($matches[1]); $i++) {
                    $jsonPhone .= '{"Phone": "' . $matches[2][$i] . '", "Info":"' . $this->cleanDepText(trim($matches[1][$i])) . '"},';
                    $phoneText = str_replace(array($matches[1][$i], $matches[2][$i]), '', $phoneText);
                }
                if (preg_match_all('~([0-9\-\(\)\s\+\/\[\]\.]{4,})~ims', $phoneText, $remains)) {
                    foreach ($remains[1] as $m) {
                        $jsonPhone .= '{"Phone": "' . $m . '", "Info":""},';
                    }
                }
            } elseif (preg_match_all('~([0-9\-\(\)\s\+\/\[\]\.]{6,})~ims', $phoneText, $matches)) {
                for ($i = 0; $i < count($matches[1]); $i++) {
                    $jsonPhone .= '{"Phone": "' . $matches[1][$i] . '", "Info":""},';
                }
            }
        }

        $jsonPhone = rtrim($jsonPhone, ',') . "]";
        return $jsonPhone == "[]" ? $originalPhone : $jsonPhone;
    }

    public function creatEmailJson($emails) {
        $originalEmail = $emails;
        $jsonEmail = "[";
        if (preg_match_all('~(?P<dep>[a-zA-Z\s&\(\)\']+)\s*[\:\-]\s*(?P<email>(?:[a-zA-Z0-9_.+-]+)(?:\W{0,1}@\W{0,1}|(?:\s*(?:\[|\()\s*)(?:[aä]tt?|bei)(?:\s*(?:\]|\))\s*)|\W+kukac\W{0,2}|\s{0,1}\(@\)\s{0,1})(?:[a-zA-Z0-9-]+)(?:\W+(?:punckt|pun[ck]t|dot|pont)\W+|\s{0,1}\(?\.\)?\s{0,1})[a-zA-Z0-9-._]{2,})~ims', $emails, $matches)) {
            for ($i = 0; $i < count($matches['dep']); $i++) {
                $jsonEmail .= '{"Email": "' . $matches['email'][$i] . '", "Info":"' . $this->cleanDepText(trim($matches['dep'][$i])) . '"},';
                $emails = str_replace(array($matches['email'][$i], $matches['dep'][$i]), '', $emails);
            }

            if (preg_match_all('~((?:[a-zA-Z0-9_.+-]+)(?:\W{0,1}@\W{0,1}|(?:\s*(?:\[|\()\s*)(?:[aä]tt?|bei)(?:\s*(?:\]|\))\s*)|\W+kukac\W{0,2}|\s{0,1}\(@\)\s{0,1})(?:[a-zA-Z0-9-]+)(?:\W+(?:punckt|pun[ck]t|dot|pont)\W+|\s{0,1}\(?\.\)?\s{0,1})[a-zA-Z0-9-._]{2,})~ims', $emails, $remains)) {
                foreach ($remains[1] as $re) {
                    $jsonEmail .= '{"Email": "' . $re . '", "Info":""},';
                }
            }
        } elseif (preg_match_all('~((?:[a-zA-Z0-9_.+-]+)(?:\W{0,1}@\W{0,1}|(?:\s*(?:\[|\()\s*)(?:[aä]tt?|bei)(?:\s*(?:\]|\))\s*)|\W+kukac\W{0,2}|\s{0,1}\(@\)\s{0,1})(?:[a-zA-Z0-9-]+)(?:\W+(?:punckt|pun[ck]t|dot|pont)\W+|\s{0,1}\(?\.\)?\s{0,1})[a-zA-Z0-9-._]{2,})((?:(?!(?:(?:(?:[a-zA-Z0-9_.+-]+)(?:\W{0,1}@\W{0,1}|(?:\s*(?:\[|\()\s*)(?:[aä]tt?|bei)(?:\s*(?:\]|\))\s*)|\W+kukac\W{0,2}|\s{0,1}\(@\)\s{0,1})(?:[a-zA-Z0-9-]+)(?:\W+(?:punckt|pun[ck]t|dot|pont)\W+|\s{0,1}\(?\.\)?\s{0,1})[a-zA-Z0-9-._]{2,}))|#|/|:).)*)~ims', $emails, $matches)) {
            for ($i = 0; $i < count($matches[1]); $i++) {
                $jsonEmail .= '{"Email": "' . $matches[1][$i] . '", "Info":"' . $this->cleanDepText(trim($matches[2][$i])) . '"},';
                $emails = str_replace(array($matches[1][$i], $matches[2][$i]), '', $emails);
            }

            if (preg_match_all('~((?:[a-zA-Z0-9_.+-]+)(?:\W{0,1}@\W{0,1}|(?:\s*(?:\[|\()\s*)(?:[aä]tt?|bei)(?:\s*(?:\]|\))\s*)|\W+kukac\W{0,2}|\s{0,1}\(@\)\s{0,1})(?:[a-zA-Z0-9-]+)(?:\W+(?:punckt|pun[ck]t|dot|pont)\W+|\s{0,1}\(?\.\)?\s{0,1})[a-zA-Z0-9-._]{2,})~ims', $emails, $remains)) {
                foreach ($remains[1] as $re) {
                    $jsonEmail .= '{"Email": "' . $re . '", "Info":""},';
                }
            }
        } else
        if (preg_match_all('~((?:[a-zA-Z0-9_.+-]+)(?:\W{0,1}@\W{0,1}|(?:\s*(?:\[|\()\s*)(?:[aä]tt?|bei)(?:\s*(?:\]|\))\s*)|\W+kukac\W{0,2}|\s{0,1}\(@\)\s{0,1})(?:[a-zA-Z0-9-]+)(?:\W+(?:punckt|pun[ck]t|dot|pont)\W+|\s{0,1}\(?\.\)?\s{0,1})[a-zA-Z0-9-._]{2,})~ims', $emails, $secEmail)) {
            foreach ($secEmail[1] as $se) {
                $jsonEmail .= '{"Email": "' . $se . '", "Info":""},';
                $emails = str_replace(array($se), '', $email->email);
            }
        }

        $jsonEmail = rtrim($jsonEmail, ",") . "]";
        return $jsonEmail == "[]" ? $originalEmail : $jsonEmail;;
    }

    public function getSocialMedia(&$urlString, $type) {
        $regex = array(
            "linkedin" => '~(((?:https*\:\/\/)?)((?:www\.)?)linkedin\.com\/(#!\/)?.+?)(?:\s|,|$)~ims',
            "twitter" => '~(((?:https*\:\/\/)?)((?:www\.)?)twitter\.com\/(#!\/)?.+?)(?:\s|,|$)~ims',
            "youtube" => '~(((?:https*\:\/\/)?)((?:www\.)?)youtube\.com\/(#!\/)?.+?)(?:\s|,|$)~ims',
            "flickr" => '~(((?:https*\:\/\/)?)((?:www\.)?)flickr\.com\/(#!\/)?.+?)(?:\s|,|$)~ims',
            "foursquare" => '~(((?:https*\:\/\/)?)((?:www\.)?)foursquare\.com\/(#!\/)?.+?)(?:\s|,|$)~ims',
            "google" => '~(((?:https*\:\/\/)?)((?:www\.)?)((?:plus\.)?)google\.com\/(#!\/)?.+?)(?:\s|,|$)~ims',
            "pinterest" => '~(((?:https*\:\/\/)?)((?:www\.)?)((?:de\.)?)pinterest\.com\/(#!\/)?.+?)(?:\s|,|$)~ims',
            "xing" => '~(((?:https*\:\/\/)?)((?:www\.)?)xing\.com\/(#!\/)?.+?)(?:\s|,|$)~ims',
        );

        if (preg_match_all($regex[$type], $urlString, $url)) {
            $urlString = str_replace($url[1], '', $urlString);
            return implode('#', $url[1]);
        }
        return "";
    }

    public function deleteUnusedSocialLinks($urlString) {
        $unsused = array('~(((?:https*\:\/\/)?)((?:www\.)?)bing\.com\/(#!\/)?.+?)(?:\s|,|$)~ims',
            '~(((?:https*\:\/\/)?)((?:www\.)?)(#!\/)?[^\s]+\.tumblr\.com\/*)(?:\s|$)~ims');

        foreach ($unsused as $un) {
            if (preg_match_all($un, $urlString, $url)) {
                $urlString = str_replace($url[1], '', $urlString);
            }
        }

        return $urlString;
    }

}
