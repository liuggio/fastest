<?php

namespace Liuggio\Fastest\Event;

final class EventsName
{
    const CHANNEL_MANAGER_STARTED = 'channel_manager_completed';
    const CHANNEL_IS_WAITING_A_COMMAND_LINE = 'channel_is_waiting_a_command_line';
    const CHANNEL_IS_WAITING_A_BEFORE_COMMAND_LINE = 'channel_is_waiting_a_before_command_line';
    const PROCESS_STARTED = 'process_started';
    const PROCESS_COMPLETED = 'process_completed';
    const PROCESS_GENERATED_BUFFER = 'process_generated_buffer';

    const INPUT_LINE_ENQUEUED = 'command_line_enqueued';
    const INPUT_LINE_DEQUEUED = 'command_line_dequeued';
    const LOOP_STARTED = 'loop_started';
    const LOOP_COMPLETED = 'loop_completed';
    const CHANNEL_IS_WAITING = 'channel_is_waiting';
    const QUEUE_IS_EMPTY = 'queue_is_empty';
    const QUEUE_IS_FROZEN = 'queue_is_frozen';
}
