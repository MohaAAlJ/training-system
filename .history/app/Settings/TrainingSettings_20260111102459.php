class TrainingSettings extends Settings
{
    // ... properties

    public static function group(): string
    {
        return 'general'; // <--- This MUST remain 'general' because that's what is in your database migration
    }
}