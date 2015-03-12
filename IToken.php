<?php

namespace Jacere\Skhema;

interface IToken
{
    /**
     * @return string
     */
    public function name();

    /**
     * @return int
     */
    public function type();

    /**
     * @return bool
     */
    public function anonymous();

    /**
     * @return string
     */
    public function __toString();
}
