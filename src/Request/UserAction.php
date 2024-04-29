<?php

namespace MagicObject\Request;

class UserAction
{
    const INSERT = "insert";
    const UPDATE = "update";
    const DELETE = "delete";
    const ACTIVATE = "activate";
    const DEACTIVATE = "deactivate";
    const APPROVE = "approve";
    const REJECT = "reject";
    const SPECIAL_ACTION = "special_action";
}