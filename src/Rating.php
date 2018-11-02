<?php
namespace Adrenalads\CommerceApi;

class Rating
{

    protected $url;
    protected $reviews_count;
    protected $raw_score;
    protected $score;

    public function __construct($attributes = [])
    {
        foreach ($attributes as $k => $v) {
            $this->$k = $v;
        }
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getReviewsCount()
    {
        return $this->reviews_count;
    }

    public function getRawScore()
    {
        return $this->raw_score;
    }

    public function getScoreImageURL()
    {
        if ($this->hasScore()) {
            return "/images/rating/" . $this->getScore() . ".gif";
        }
    }

    public function hasScore()
    {
        return $this->getScore() > 0;
    }

    public function getScore()
    {
        return intval($this->score);
    }

}
