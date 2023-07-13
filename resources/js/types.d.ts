declare global {
    interface Task {
        id: number,
        description: string,
        subtasks: Task[]
    }
}
export {}
