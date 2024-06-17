<?php

declare(strict_types=1);
/*
 * Copyright (C) 2016-2024 Taylor & Hart Limited
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains the property
 * of Taylor & Hart Limited and its suppliers, if any.
 *
 * All   intellectual   and  technical  concepts  contained  herein  are
 * proprietary  to  Taylor & Hart Limited  and  its suppliers and may be
 * covered  by  U.K.  and  foreign  patents, patents in process, and are
 * protected in full by copyright law. Dissemination of this information
 * or  reproduction  of this material is strictly forbidden unless prior
 * written permission is obtained from Taylor & Hart Limited.
 *
 * ANY  REPRODUCTION, MODIFICATION, DISTRIBUTION, PUBLIC PERFORMANCE, OR
 * PUBLIC  DISPLAY  OF  OR  THROUGH  USE OF THIS SOURCE CODE WITHOUT THE
 * EXPRESS  WRITTEN CONSENT OF RARE PINK LIMITED IS STRICTLY PROHIBITED,
 * AND  IN  VIOLATION  OF  APPLICABLE LAWS. THE RECEIPT OR POSSESSION OF
 * THIS  SOURCE CODE AND/OR RELATED INFORMATION DOES NOT CONVEY OR IMPLY
 * ANY  RIGHTS  TO REPRODUCE, DISCLOSE OR DISTRIBUTE ITS CONTENTS, OR TO
 * MANUFACTURE,  USE, OR SELL ANYTHING THAT IT MAY DESCRIBE, IN WHOLE OR
 * IN PART.
 */

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Arxy\TranslationsBundle\CacheFlag;
use Arxy\TranslationsBundle\Dumper\DatabaseDumper;
use Arxy\TranslationsBundle\Repository;

return static function (ContainerConfigurator $container) {
    $services = $container->services()
        ->defaults()
        ->autoconfigure()
        ->autowire();

    $services->set(DatabaseDumper::class)->args([
        service(Repository::class),
    ])
        ->tag('translation.dumper', ['alias' => 'db']);

    $services->set(CacheFlag::class);
};
