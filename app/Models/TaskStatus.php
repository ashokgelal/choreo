<?php
namespace App\Models;

enum TaskStatus : string {
    case TODO = "todo";
    case IN_PROGRESS = "in progress";
    case DONE = "done";
}
