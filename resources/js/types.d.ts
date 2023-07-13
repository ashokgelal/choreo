declare global {
    interface Task {
        id: number,
        description: string,
        subtasks: Task[],
        status: string,
        parent_task_id: number,
    }
}
export {}
