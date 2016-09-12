<?php

/*
 * This file is part of the Speedwork package.
 *
 * (c) Sankar <sankar.suda@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Speedwork\Util\Interfaces;

/**
 * @author Sankar <sankar.suda@gmail.com>
 */
interface MessageProvider
{
    /**
     * Get the messages for the instance.
     *
     * @return \Speedwork\Util\Interfaces\MessageBag
     */
    public function getMessageBag();
}
