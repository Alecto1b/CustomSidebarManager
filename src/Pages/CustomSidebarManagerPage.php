<?php

namespace CustomSidebarManager\Pages;

use CustomSidebarManager\Support\CustomSidebarManagerPageResolver;

if (CustomSidebarManagerPageResolver::usesFilamentFive()) {
    class CustomSidebarManagerPage extends Filament5CustomSidebarManagerPage
    {
    }
} else {
    class CustomSidebarManagerPage extends Filament3CustomSidebarManagerPage
    {
    }
}
